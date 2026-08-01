<?php
namespace App\Controllers;
use App\Models\ContactModel;
use App\Libraries\ContactService;
use App\Libraries\ContactImportService;

class Contacts extends BaseController
{
    public function index(): string
    {
        $model=new ContactModel(); $q=trim((string)$this->request->getGet('q'));
        $model->where('workspace_id',$this->workspaceId); if($q!=='') $model->groupStart()->like('email',$q)->orLike('first_name',$q)->orLike('last_name',$q)->groupEnd();
        return $this->page('contacts/index',['title'=>'Contacts','active'=>'contacts','contacts'=>$model->orderBy('id','DESC')->paginate(25),'pager'=>$model->pager,'q'=>$q,'imports'=>db_connect()->table('contact_imports')->where('workspace_id',$this->workspaceId)->orderBy('id','DESC')->limit(5)->get()->getResultArray(),'apiKeys'=>db_connect()->table('contact_api_keys')->where('workspace_id',$this->workspaceId)->orderBy('id','DESC')->get()->getResultArray()]);
    }
    public function create()
    {
        $data=$this->request->getPost(['email','first_name','last_name','phone','birthday','company','job_title','website','city','state','country','notes']);
        try{(new ContactService())->create(current_user(),$data+['status'=>'subscribed','source'=>'manual']);}catch(\Throwable $e){return redirect()->back()->withInput()->with('error',$e->getMessage());}
        return redirect()->to('/app/contacts')->with('success','Contact added successfully.');
    }
    public function import()
    {
        $file=$this->request->getFile('contacts_file');if(!$file)return redirect()->back()->with('error','Choose a file.');try{$uuid=(new ContactImportService())->stage(current_user(),$file,['duplicate_mode'=>$this->request->getPost('duplicate_mode')?:'skip']);}catch(\Throwable$e){return redirect()->back()->with('error',$e->getMessage());}return redirect()->to('/app/contacts')->with('success',"Import {$uuid} was queued. Run php spark imports:process to process it.");
    }
    public function deleteDuplicates()
    {
        $db=db_connect(); $rows=$db->query('SELECT email, MIN(id) keep_id FROM contacts WHERE workspace_id = ? AND deleted_at IS NULL GROUP BY email HAVING COUNT(*) > 1',[$this->workspaceId])->getResultArray(); $removed=0;
        foreach($rows as $row) $removed += $db->table('contacts')->where('workspace_id',$this->workspaceId)->where('email',$row['email'])->where('id !=',$row['keep_id'])->delete();
        return redirect()->to('/app/contacts')->with('success',"Removed {$removed} duplicate contacts.");
    }
    public function createApiKey()
    {
        $name=trim((string)$this->request->getPost('name'));if($name==='')return redirect()->back()->with('error','API key name is required.');$db=db_connect();$workspace=$db->table('workspaces')->where('id',$this->workspaceId)->get()->getRowArray();$used=$db->table('contact_api_keys')->where(['workspace_id'=>$this->workspaceId,'status'=>'active'])->countAllResults();if($workspace['api_key_limit']!==null&&$used>=(int)$workspace['api_key_limit'])return redirect()->back()->with('error','Workspace API key limit reached.');$raw='mlr_'.bin2hex(random_bytes(24));$db->table('contact_api_keys')->insert(['uuid'=>\App\Support\Uuid::v4(),'workspace_id'=>$this->workspaceId,'name'=>$name,'key_prefix'=>substr($raw,0,12),'secret_hash'=>hash('sha256',$raw),'status'=>'active','created_by'=>current_user()['id'],'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);return redirect()->back()->with('success','API key created. Copy it now: '.$raw);
    }
    public function revokeApiKey(string$uuid){db_connect()->table('contact_api_keys')->where(['uuid'=>$uuid,'workspace_id'=>$this->workspaceId])->update(['status'=>'revoked','updated_at'=>date('Y-m-d H:i:s')]);return redirect()->back()->with('success','API key revoked.');}
}
