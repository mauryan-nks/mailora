<?php
namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ContactModel;
use App\Support\Uuid;

class Contacts extends BaseController
{
    public function create()
    {
        $raw=trim((string)$this->request->getHeaderLine('X-API-Key'));$db=db_connect();$key=$raw===''?null:$db->table('contact_api_keys')->where(['secret_hash'=>hash('sha256',$raw),'status'=>'active'])->get()->getRowArray();if(!$key)return $this->response->setStatusCode(401)->setJSON(['error'=>'Invalid API key.']);
        $workspace=$db->table('workspaces')->where('id',$key['workspace_id'])->get()->getRowArray();if(!$workspace)return $this->response->setStatusCode(404)->setJSON(['error'=>'Workspace not found.']);$data=$this->request->getJSON(true)?:$this->request->getPost();$email=strtolower(trim((string)($data['email']??'')));if(!filter_var($email,FILTER_VALIDATE_EMAIL))return $this->response->setStatusCode(422)->setJSON(['error'=>'A valid email is required.']);if($db->table('contacts')->where(['workspace_id'=>$workspace['id'],'normalized_email'=>$email,'deleted_at'=>null])->countAllResults())return $this->response->setStatusCode(409)->setJSON(['error'=>'Contact already exists.']);if($workspace['contact_limit']!==null&&$db->table('contacts')->where(['workspace_id'=>$workspace['id'],'deleted_at'=>null])->countAllResults()>=(int)$workspace['contact_limit'])return $this->response->setStatusCode(429)->setJSON(['error'=>'Contact limit reached.']);
        $model=new ContactModel();$id=$model->insert(['uuid'=>Uuid::v4(),'workspace_id'=>$workspace['id'],'reseller_id'=>$workspace['reseller_id']?:null,'email'=>$email,'normalized_email'=>$email,'first_name'=>$data['first_name']??null,'last_name'=>$data['last_name']??null,'phone'=>$data['phone']??null,'company'=>$data['company']??null,'status'=>'subscribed','source'=>'api','consent_status'=>'granted','consent_source'=>$data['consent_source']??'website_form','consented_at'=>date('Y-m-d H:i:s'),'created_by'=>$key['created_by']]);if(!$id)return $this->response->setStatusCode(422)->setJSON(['error'=>implode(' ',$model->errors())]);$db->table('contact_api_keys')->where('id',$key['id'])->update(['last_used_at'=>date('Y-m-d H:i:s')]);$db->table('workspaces')->where('id',$workspace['id'])->set('contacts_used','contacts_used+1',false)->update();return $this->response->setStatusCode(201)->setJSON(['id'=>$model->find($id)['uuid'],'message'=>'Contact created.']);
    }
}
