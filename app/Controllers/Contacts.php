<?php
namespace App\Controllers;
use App\Models\ContactModel;

class Contacts extends BaseController
{
    public function index(): string
    {
        $model=new ContactModel(); $q=trim((string)$this->request->getGet('q'));
        $model->where('workspace_id',$this->workspaceId); if($q!=='') $model->groupStart()->like('email',$q)->orLike('first_name',$q)->orLike('last_name',$q)->groupEnd();
        return $this->page('contacts/index',['title'=>'Contacts','active'=>'contacts','contacts'=>$model->orderBy('id','DESC')->paginate(25),'pager'=>$model->pager,'q'=>$q]);
    }
    public function create()
    {
        $model=new ContactModel(); $data=$this->request->getPost(['email','first_name','last_name','phone','birthday']); $data+=['workspace_id'=>$this->workspaceId,'status'=>'subscribed','source'=>'manual'];
        if(!$model->insert($data)) return redirect()->back()->withInput()->with('error',implode(' ', $model->errors()));
        return redirect()->to('/contacts')->with('success','Contact added successfully.');
    }
    public function import()
    {
        $file=$this->request->getFile('contacts_file'); if(!$file || !$file->isValid()) return redirect()->back()->with('error','Choose a valid CSV file.');
        $handle=fopen($file->getTempName(),'r'); $headers=array_map(fn($h)=>strtolower(trim($h)), fgetcsv($handle) ?: []); $model=new ContactModel(); $created=0;
        while(($row=fgetcsv($handle))!==false){ $item=array_combine($headers,array_pad($row,count($headers),'')); if(!$item || empty($item['email'])) continue; $payload=['workspace_id'=>$this->workspaceId,'email'=>strtolower(trim($item['email'])),'first_name'=>$item['first_name']??'','last_name'=>$item['last_name']??'','phone'=>$item['phone']??'','status'=>'subscribed','source'=>'csv']; if(!$model->where(['workspace_id'=>$this->workspaceId,'email'=>$payload['email']])->first() && $model->insert($payload)) $created++; }
        fclose($handle); return redirect()->to('/contacts')->with('success',"Imported {$created} new contacts.");
    }
    public function deleteDuplicates()
    {
        $db=db_connect(); $rows=$db->query('SELECT email, MIN(id) keep_id FROM contacts WHERE workspace_id = ? AND deleted_at IS NULL GROUP BY email HAVING COUNT(*) > 1',[$this->workspaceId])->getResultArray(); $removed=0;
        foreach($rows as $row) $removed += $db->table('contacts')->where('workspace_id',$this->workspaceId)->where('email',$row['email'])->where('id !=',$row['keep_id'])->delete();
        return redirect()->to('/contacts')->with('success',"Removed {$removed} duplicate contacts.");
    }
}
