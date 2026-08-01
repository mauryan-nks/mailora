<?php
namespace App\Controllers;
use App\Libraries\AuditLogService;use App\Libraries\WorkspaceAccessService;
class Workspaces extends BaseController
{
    public function index():string
    {
        $user=current_user();if(!in_array($user['account_type'],['platform_admin','platform_team','reseller','reseller_team'],true))throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $builder=db_connect()->table('workspaces w')->select('w.*,u.first_name,u.last_name,u.email,(SELECT COUNT(*) FROM contacts c WHERE c.workspace_id=w.id AND c.deleted_at IS NULL) contacts_count,(SELECT COUNT(*) FROM campaigns ca WHERE ca.workspace_id=w.id) campaigns_count')->join('users u','u.id=w.owner_user_id','left');
        if($user['account_type']==='reseller')$builder->where('w.reseller_id',$user['id']);elseif($user['account_type']==='reseller_team')$builder->join('user_workspace_assignments a','a.workspace_id=w.id')->where('a.user_id',$user['id']);elseif($user['account_type']==='platform_team')$builder->join('user_workspace_assignments a','a.workspace_id=w.id')->where('a.user_id',$user['id']);
        return$this->page('workspaces/index',['title'=>'Client workspaces','active'=>'clients','workspaces'=>$builder->orderBy('w.id','DESC')->get()->getResultArray()]);
    }
    public function open(string$uuid){$user=current_user();$workspace=db_connect()->table('workspaces')->where('uuid',$uuid)->get()->getRowArray();if(!$workspace||!(new WorkspaceAccessService())->canAccess($user,(int)$workspace['id']))throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();session()->set('active_workspace_id',(int)$workspace['id']);(new AuditLogService())->record('workspace.opened',$user,[],'workspace',$workspace['uuid']);return redirect()->to('/app/dashboard')->with('success','Opened '.$workspace['company_name'].'.');}
    public function close(){session()->remove('active_workspace_id');return redirect()->to(portal_home())->with('success','Returned to your main panel.');}
}
