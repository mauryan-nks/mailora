<?php
namespace App\Libraries;
class WorkspaceAccessService
{
    public function canAccess(array $user,int $workspaceId):bool
    {
        return match($user['account_type']){
            'platform_admin'=>true,
            'platform_team','reseller_team'=>$this->assigned($user,$workspaceId),
            'reseller'=>db_connect()->table('workspaces')->where(['id'=>$workspaceId,'reseller_id'=>$user['id']])->countAllResults()>0,
            'customer'=>(int)$user['workspace_id']===$workspaceId,
            default=>false,
        };
    }
    private function assigned(array $u,int $id):bool { if($u['account_type']==='reseller_team'&&db_connect()->table('workspaces')->where(['id'=>$id,'reseller_id'=>$u['reseller_id']])->countAllResults()===0)return false;return db_connect()->table('user_workspace_assignments')->where(['user_id'=>$u['id'],'workspace_id'=>$id])->countAllResults()>0; }
}
