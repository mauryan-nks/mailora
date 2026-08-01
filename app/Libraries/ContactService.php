<?php
namespace App\Libraries;
use App\Models\ContactModel;use App\Support\Uuid;
class ContactService
{
    public function create(array$user,array$data):array
    {
        $workspaceId=(int)$user['workspace_id'];
        if(!$workspaceId)throw new \DomainException('A customer workspace is required.');
        if(!(new PermissionService())->allows($user,'contacts.create',$workspaceId))throw new \DomainException('Permission denied.');
        $db=db_connect();$workspace=$db->table('workspaces')->where('id',$workspaceId)->get()->getRowArray();
        $used=$db->table('contacts')->where(['workspace_id'=>$workspaceId,'deleted_at'=>null])->countAllResults();
        if($workspace['contact_limit']!==null&&$used>=(int)$workspace['contact_limit'])throw new \DomainException('Workspace contact limit reached.');
        $email=$this->normalizeEmail((string)($data['email']??''));
        if(!filter_var($email,FILTER_VALIDATE_EMAIL))throw new \InvalidArgumentException('A valid email address is required.');
        if($db->table('contact_suppressions')->where(['workspace_id'=>$workspaceId,'email'=>$email])->countAllResults()>0)$data['status']='suppressed';
        $payload=array_merge($data,['uuid'=>Uuid::v4(),'workspace_id'=>$workspaceId,'reseller_id'=>$user['reseller_id']?:null,'email'=>$email,'normalized_email'=>$email,'status'=>$data['status']??'subscribed','source'=>$data['source']??'manual','created_by'=>$user['id']]);
        $model=new ContactModel();$id=$model->insert($payload);if(!$id)throw new \RuntimeException(implode(' ',$model->errors()));
        $db->table('contact_activities')->insert(['workspace_id'=>$workspaceId,'contact_id'=>$id,'user_id'=>$user['id'],'event_type'=>'contact.created','description'=>'Contact created','metadata'=>json_encode(['source'=>$payload['source']]),'occurred_at'=>date('Y-m-d H:i:s')]);
        $db->table('workspaces')->where('id',$workspaceId)->set('contacts_used','contacts_used+1',false)->update();
        return$model->find($id);
    }
    public function normalizeEmail(string$email):string{return strtolower(trim($email));}
    public function canReceive(array$contact):bool{if($contact['status']!=='subscribed')return false;return db_connect()->table('contact_suppressions')->where(['workspace_id'=>$contact['workspace_id'],'email'=>$contact['normalized_email']])->countAllResults()===0;}
}
