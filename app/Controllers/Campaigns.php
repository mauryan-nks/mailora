<?php
namespace App\Controllers;

use App\Libraries\CampaignService;
use App\Models\CampaignModel;

class Campaigns extends BaseController
{
    public function index(): string
    {
        $m = new CampaignModel();
        return $this->page('campaigns/index', [
            'title' => 'Campaigns',
            'active' => 'campaigns',
            'campaigns' => $m->where('workspace_id', $this->workspaceId)->orderBy('id', 'DESC')->findAll(),
        ]);
    }

    public function new(): string
    {
        $templates = db_connect()->table('templates')->where('workspace_id', $this->workspaceId)->orderBy('name')->get()->getResultArray();
        $segments = db_connect()->table('segments')->where('workspace_id', $this->workspaceId)->orderBy('name')->get()->getResultArray();
        $smtpAccounts = db_connect()->table('smtp_accounts')->where('workspace_id', $this->workspaceId)->orderBy('is_active', 'DESC')->orderBy('id', 'DESC')->get()->getResultArray();

        return $this->page('campaigns/form', [
            'title' => 'Create campaign',
            'active' => 'campaigns',
            'campaign' => null,
            'templates' => $templates,
            'segments' => $segments,
            'smtpAccounts' => $smtpAccounts,
        ]);
    }

    public function create()
    {
        $data = $this->payload();
        $m = new CampaignModel();

        if (! $m->insert($data)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $m->errors()));
        }

        return redirect()->to('/app/campaigns')->with('success', 'Campaign saved as ' . $data['status'] . '.');
    }

    public function edit(int $id): string
    {
        $m = new CampaignModel();
        $campaign = $m->where('workspace_id', $this->workspaceId)->find($id);

        if (! $campaign) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $templates = db_connect()->table('templates')->where('workspace_id', $this->workspaceId)->orderBy('name')->get()->getResultArray();
        $segments = db_connect()->table('segments')->where('workspace_id', $this->workspaceId)->orderBy('name')->get()->getResultArray();
        $smtpAccounts = db_connect()->table('smtp_accounts')->where('workspace_id', $this->workspaceId)->orderBy('is_active', 'DESC')->orderBy('id', 'DESC')->get()->getResultArray();

        return $this->page('campaigns/form', [
            'title' => 'Edit campaign',
            'active' => 'campaigns',
            'campaign' => $campaign,
            'templates' => $templates,
            'segments' => $segments,
            'smtpAccounts' => $smtpAccounts,
        ]);
    }

    public function update(int $id)
    {
        $m = new CampaignModel();
        $existing = $m->find($id);

        if (! $existing || (int) $existing['workspace_id'] !== $this->workspaceId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (! $m->update($id, $this->payload())) {
            return redirect()->back()->withInput()->with('error', implode(' ', $m->errors()));
        }

        return redirect()->to('/app/campaigns')->with('success', 'Campaign updated.');
    }

    public function test(int $id)
    {
        try {
            (new CampaignService($this->workspaceId))->sendTest($id, current_user()['email'] ?? '');

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Test email sent successfully.',
                ]);
            }

            return redirect()->back()->with('success', 'Test email sent successfully.');
        } catch (\Throwable $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'error' => 'Unable to send test email: ' . $e->getMessage(),
                ]);
            }

            return redirect()->back()->with('error', 'Unable to send test email: ' . $e->getMessage());
        }
    }

    public function send(int $id)
    {
        try {
            $counts = (new CampaignService($this->workspaceId))->sendCampaign($id);
            return redirect()->to('/app/campaigns')->with('success', "Campaign sent to {$counts['delivered']} recipients ({$counts['bounced']} bounces).");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Unable to send campaign: ' . $e->getMessage());
        }
    }

    private function payload(): array
    {
        $scheduled = $this->request->getPost('scheduled_at');

        return [
            'workspace_id' => $this->workspaceId,
            'name' => (string) $this->request->getPost('name'),
            'subject' => (string) $this->request->getPost('subject'),
            'preview_text' => (string) $this->request->getPost('preview_text'),
            'from_name' => (string) $this->request->getPost('from_name'),
            'from_email' => (string) $this->request->getPost('from_email'),
            'content_html' => (string) $this->request->getPost('content_html'),
            'content_text' => strip_tags((string) $this->request->getPost('content_html')),
            'editor_type' => (string) ($this->request->getPost('editor_type') ?: 'richtext'),
            'smtp_id' => $this->request->getPost('smtp_id') ? (int) $this->request->getPost('smtp_id') : null,
            'status' => $scheduled ? 'scheduled' : 'draft',
            'scheduled_at' => $scheduled ?: null,
            'timezone' => (string) ($this->request->getPost('timezone') ?: 'Asia/Kolkata'),
            'template_id' => $this->request->getPost('template_id') ? (int) $this->request->getPost('template_id') : null,
            'segment_id' => $this->request->getPost('segment_id') ? (int) $this->request->getPost('segment_id') : null,
        ];
    }
}
