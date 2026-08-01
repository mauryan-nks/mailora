<?php
namespace App\Controllers;
class Settings extends BaseController
{
    public function index(): string
    {
        $db = db_connect();
        $workspace = $db->table('workspaces')
            ->where('id', $this->workspaceId)
            ->get()
            ->getRowArray();

        if ($workspace === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                'Workspace not found. Run the DemoSeeder or create a workspace first.'
            );
        }

        return $this->page('settings/index', [
            'title'     => 'Settings',
            'active'    => 'settings',
            'workspace' => $workspace,
            'smtp'      => $db->table('smtp_accounts')
                ->where('workspace_id', $this->workspaceId)
                ->get()
                ->getRowArray(),
        ]);
    }
    public function team(): string { return $this->page('simple/cards',['title'=>'Team','active'=>'team','subtitle'=>'Members, roles and workspace access','items'=>db_connect()->table('team_members')->where('workspace_id',$this->workspaceId)->get()->getResultArray()]); }
    public function save() { $db=db_connect(); $db->table('workspaces')->where('id',$this->workspaceId)->update(['name'=>$this->request->getPost('name'),'timezone'=>$this->request->getPost('timezone'),'brand_color'=>$this->request->getPost('brand_color'),'custom_domain'=>$this->request->getPost('custom_domain'),'updated_at'=>date('Y-m-d H:i:s')]); return redirect()->back()->with('success','Workspace settings saved.'); }
}
