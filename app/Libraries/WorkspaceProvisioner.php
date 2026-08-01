<?php
namespace App\Libraries;
use App\Models\UserModel;use App\Support\Uuid;
class WorkspaceProvisioner
{
    public function ensureForUser(array $user): int
    {
        $db=db_connect();if(!empty($user['workspace_id']))return(int)$user['workspace_id'];$member=$db->table('workspace_members')->where('user_id',$user['id'])->get()->getRowArray();if($member){(new UserModel())->update($user['id'],['workspace_id'=>$member['workspace_id'],'workspace_role'=>$member['role']]);return(int)$member['workspace_id'];}
        $name=trim((string)($user['company_name']?:$user['first_name']?:$user['username']?:'My Workspace'));$slugBase=url_title($name,'-',true)?:'workspace';$slug=$slugBase;$n=1;while($db->table('workspaces')->where('slug',$slug)->countAllResults()>0)$slug=$slugBase.'-'.++$n;$now=date('Y-m-d H:i:s');$db->transStart();$db->table('workspaces')->insert(['uuid'=>Uuid::v4(),'reseller_id'=>$user['reseller_id']?:null,'owner_user_id'=>$user['id'],'company_name'=>$name,'name'=>$name,'slug'=>$slug,'timezone'=>$user['timezone']?:'Asia/Kolkata','status'=>'trial','trial_ends_at'=>date('Y-m-d H:i:s',strtotime('+14 days')),'created_at'=>$now,'updated_at'=>$now]);$workspaceId=(int)$db->insertID();$db->table('workspace_members')->insert(['workspace_id'=>$workspaceId,'user_id'=>$user['id'],'role'=>'owner','created_at'=>$now,'updated_at'=>$now]);$db->transComplete();if(!$db->transStatus())throw new \RuntimeException('Unable to provision workspace.');(new UserModel())->update($user['id'],['workspace_id'=>$workspaceId,'workspace_role'=>'owner']);return$workspaceId;
    }
}
