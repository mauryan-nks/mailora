<?php
namespace App\Controllers;
class Dashboard extends BaseController
{
    public function index():string
    {
        $user=current_user();$selected=(int)session('active_workspace_id');
        if($selected||$user['account_type']==='customer')return$this->customer();
        if(in_array($user['account_type'],['reseller','reseller_team'],true))return$this->reseller($user);
        return$this->platform($user);
    }
    private function platform(array$user):string
    {
        $db=db_connect();$workspaceBuilder=$db->table('workspaces w')->select('w.*,u.first_name,u.last_name,u.email,(SELECT COUNT(*) FROM contacts c WHERE c.workspace_id=w.id AND c.deleted_at IS NULL) contacts_count,(SELECT COUNT(*) FROM campaigns ca WHERE ca.workspace_id=w.id) campaigns_count')->join('users u','u.id=w.owner_user_id','left');if($user['account_type']==='platform_team')$workspaceBuilder->join('user_workspace_assignments a','a.workspace_id=w.id')->where('a.user_id',$user['id']);
        return$this->page('dashboard/platform',['title'=>'Platform overview','active'=>'dashboard','stats'=>['resellers'=>$db->table('users')->where(['account_type'=>'reseller','deleted_at'=>null])->countAllResults(),'workspaces'=>$db->table('workspaces')->countAllResults(),'users'=>$db->table('users')->where('deleted_at',null)->countAllResults(),'emails'=>$db->table('campaign_events')->where('event_type','delivered')->countAllResults()],'workspaces'=>$workspaceBuilder->orderBy('w.id','DESC')->limit(6)->get()->getResultArray()]);
    }
    private function reseller(array$user):string
    {
        $db=db_connect();$resellerId=$user['account_type']==='reseller'?$user['id']:$user['reseller_id'];$b=$db->table('workspaces w')->select('w.*,u.first_name,u.last_name,u.email,(SELECT COUNT(*) FROM contacts c WHERE c.workspace_id=w.id AND c.deleted_at IS NULL) contacts_count,(SELECT COUNT(*) FROM campaigns ca WHERE ca.workspace_id=w.id) campaigns_count')->join('users u','u.id=w.owner_user_id','left')->where('w.reseller_id',$resellerId);if($user['account_type']==='reseller_team')$b->join('user_workspace_assignments a','a.workspace_id=w.id')->where('a.user_id',$user['id']);$workspaces=$b->orderBy('w.id','DESC')->get()->getResultArray();return$this->page('dashboard/reseller',['title'=>'Agency overview','active'=>'dashboard','workspaces'=>$workspaces,'stats'=>['clients'=>count($workspaces),'contacts'=>array_sum(array_column($workspaces,'contacts_count')),'campaigns'=>array_sum(array_column($workspaces,'campaigns_count')),'team'=>$db->table('users')->where('reseller_id',$resellerId)->where('account_type','reseller_team')->where('deleted_at',null)->countAllResults()]]);
    }
    private function customer():string
    {
        $db=db_connect();$workspace=$db->table('workspaces')->where('id',$this->workspaceId)->get()->getRowArray();if(!$workspace)throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Workspace not found.');$contacts=$db->table('contacts')->where('workspace_id',$this->workspaceId)->where('deleted_at',null)->countAllResults();$campaigns=$db->table('campaigns')->where('workspace_id',$this->workspaceId)->orderBy('id','DESC')->limit(5)->get()->getResultArray();$events=[];foreach(['delivered','open','click','bounce','spam','unsubscribe']as$type)$events[$type]=$db->table('campaign_events ce')->join('campaigns c','c.id=ce.campaign_id')->where('c.workspace_id',$this->workspaceId)->where('ce.event_type',$type)->countAllResults();return$this->page('dashboard/index',['title'=>$workspace['company_name']?:'Workspace','active'=>'dashboard','contacts'=>$contacts,'campaigns'=>$campaigns,'events'=>$events,'workspace'=>$workspace,'isSwitched'=>(bool)session('active_workspace_id')]);
    }
}
