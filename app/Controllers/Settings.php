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

        $plan = $workspace['plan_id'] ? $db->table('plans')->where('id', $workspace['plan_id'])->get()->getRowArray() : null;
        $smtpAccounts = $db->table('smtp_accounts')->where('workspace_id', $this->workspaceId)->orderBy('is_active', 'DESC')->orderBy('id', 'DESC')->get()->getResultArray();
        $selectedSmtp = null;

        if ($this->request->getGet('smtp_edit')) {
            $selectedSmtp = $db->table('smtp_accounts')->where('workspace_id', $this->workspaceId)->where('id', (int) $this->request->getGet('smtp_edit'))->get()->getRowArray();
        }

        return $this->page('settings/index', [
            'title' => 'Settings',
            'active' => 'settings',
            'workspace' => $workspace,
            'plan' => $plan,
            'smtpAccounts' => $smtpAccounts,
            'selectedSmtp' => $selectedSmtp,
        ]);
    }

    public function team(): string
    {
        return $this->page('simple/cards', [
            'title' => 'Team',
            'active' => 'team',
            'subtitle' => 'Members, roles and workspace access',
            'items' => db_connect()->table('team_members')->where('workspace_id', $this->workspaceId)->get()->getResultArray(),
        ]);
    }

    public function save()
    {
        $db = db_connect();
        $workspaceData = [
            'name' => $this->request->getPost('name'),
            'timezone' => $this->request->getPost('timezone'),
            'brand_color' => $this->request->getPost('brand_color'),
            'custom_domain' => $this->request->getPost('custom_domain'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $db->table('workspaces')->where('id', $this->workspaceId)->update($workspaceData);

        $action = $this->request->getPost('smtp_action');
        if ($action === 'save_smtp') {
            $workspace = $db->table('workspaces')->where('id', $this->workspaceId)->get()->getRowArray();
            $plan = $workspace['plan_id'] ? $db->table('plans')->where('id', $workspace['plan_id'])->get()->getRowArray() : null;
            $smtpCount = (int) $db->table('smtp_accounts')->where('workspace_id', $this->workspaceId)->countAllResults();
            $maxSmtp = $plan['max_smtp_accounts'] ?? null;

            $smtp = [
                'workspace_id' => $this->workspaceId,
                'provider' => $this->request->getPost('smtp_provider'),
                'host' => $this->request->getPost('smtp_host'),
                'port' => (int) $this->request->getPost('smtp_port'),
                'username' => $this->request->getPost('smtp_username'),
                'encryption' => $this->request->getPost('smtp_encryption') ?: 'tls',
                'from_email' => $this->request->getPost('smtp_from_email'),
                'from_name' => $this->request->getPost('smtp_from_name'),
                'is_active' => $this->request->getPost('smtp_enabled') ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $password = (string) $this->request->getPost('smtp_password');
            if ($password !== '') {
                $smtp['encrypted_password'] = service('encrypter')->encrypt($password);
            }

            $smtpId = $this->request->getPost('smtp_id');
            if (empty($smtpId)) {
                if ($maxSmtp !== null && $smtpCount >= (int) $maxSmtp) {
                    return redirect()->back()->withInput()->with('error', 'This workspace has reached its SMTP account limit.');
                }

                $smtp['created_at'] = date('Y-m-d H:i:s');
                $db->table('smtp_accounts')->insert($smtp);
                $smtpId = $db->insertID();
            } else {
                $existing = $db->table('smtp_accounts')->where('workspace_id', $this->workspaceId)->where('id', (int) $smtpId)->get()->getRowArray();
                if (! $existing) {
                    return redirect()->back()->withInput()->with('error', 'SMTP account not found.');
                }
                $db->table('smtp_accounts')->where('id', $existing['id'])->update($smtp);
            }

            if ($smtp['is_active']) {
                $db->table('smtp_accounts')
                    ->where('workspace_id', $this->workspaceId)
                    ->where('id !=', (int) $smtpId)
                    ->update(['is_active' => 0]);
            }

            return redirect()->back()->with('success', 'SMTP configuration saved.');
        }

        if ($action === 'delete_smtp') {
            $smtpId = (int) $this->request->getPost('smtp_id');
            $existing = $db->table('smtp_accounts')->where('workspace_id', $this->workspaceId)->where('id', $smtpId)->get()->getRowArray();
            if ($existing) {
                $db->table('smtp_accounts')->where('id', $smtpId)->delete();
                return redirect()->back()->with('success', 'SMTP configuration removed.');
            }
            return redirect()->back()->with('error', 'SMTP account not found.');
        }

        return redirect()->back()->with('success', 'Workspace settings saved.');
    }
}
