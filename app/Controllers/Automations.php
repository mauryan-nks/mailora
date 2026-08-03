<?php
namespace App\Controllers;

use App\Models\AutomationModel;

class Automations extends BaseController
{
    public function index(): string
    {
        $model = new AutomationModel();

        return $this->page('automations/index', [
            'title' => 'Automation',
            'active' => 'automations',
            'automations' => $model->where('workspace_id', $this->workspaceId)->orderBy('id', 'DESC')->findAll(),
        ]);
    }

    public function new(): string
    {
        $segments = db_connect()->table('segments')->where('workspace_id', $this->workspaceId)->orderBy('name')->get()->getResultArray();
        $smtpAccounts = db_connect()->table('smtp_accounts')->where('workspace_id', $this->workspaceId)->orderBy('is_active', 'DESC')->orderBy('id', 'DESC')->get()->getResultArray();

        return $this->page('automations/form', [
            'title' => 'New Automation',
            'active' => 'automations',
            'automation' => null,
            'segments' => $segments,
            'smtpAccounts' => $smtpAccounts,
        ]);
    }

    public function create()
    {
        $model = new AutomationModel();
        $data = $this->payload();

        if (! $model->insert($data)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $model->errors()));
        }

        return redirect()->to(workspace_url('automations'))->with('success', 'Automation saved.');
    }

    public function edit(int $id): string
    {
        $model = new AutomationModel();
        $automation = $model->where('workspace_id', $this->workspaceId)->find($id);

        if (! $automation) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $segments = db_connect()->table('segments')->where('workspace_id', $this->workspaceId)->orderBy('name')->get()->getResultArray();
        $smtpAccounts = db_connect()->table('smtp_accounts')->where('workspace_id', $this->workspaceId)->orderBy('is_active', 'DESC')->orderBy('id', 'DESC')->get()->getResultArray();

        return $this->page('automations/form', [
            'title' => 'Edit Automation',
            'active' => 'automations',
            'automation' => $automation,
            'segments' => $segments,
            'smtpAccounts' => $smtpAccounts,
        ]);
    }

    public function update(int $id)
    {
        $model = new AutomationModel();
        $automation = $model->find($id);

        if (! $automation || (int) $automation['workspace_id'] !== $this->workspaceId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (! $model->update($id, $this->payload())) {
            return redirect()->back()->withInput()->with('error', implode(' ', $model->errors()));
        }

        return redirect()->to(workspace_url('automations'))->with('success', 'Automation updated.');
    }

    public function delete(int $id)
    {
        $model = new AutomationModel();
        $automation = $model->find($id);

        if (! $automation || (int) $automation['workspace_id'] !== $this->workspaceId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $model->delete($id);

        return redirect()->to(workspace_url('automations'))->with('success', 'Automation deleted.');
    }

    private function payload(): array
    {
        return [
            'workspace_id' => $this->workspaceId,
            'name' => (string) $this->request->getPost('name'),
            'trigger_type' => (string) $this->request->getPost('trigger_type'),
            'trigger_event' => trim((string) $this->request->getPost('trigger_event')) ?: null,
            'flow_action' => (string) $this->request->getPost('flow_action') ?: 'send_email',
            'delay_minutes' => (int) $this->request->getPost('delay_minutes'),
            'subject' => (string) $this->request->getPost('subject'),
            'content_html' => (string) $this->request->getPost('content_html'),
            'webhook_url' => trim((string) $this->request->getPost('webhook_url')) ?: null,
            'webhook_method' => (string) $this->request->getPost('webhook_method') ?: 'POST',
            'webhook_payload' => trim((string) $this->request->getPost('webhook_payload')) ?: null,
            'status' => $this->request->getPost('status') ?? 'paused',
            'segment_id' => $this->request->getPost('segment_id') ? (int) $this->request->getPost('segment_id') : null,
            'smtp_id' => $this->request->getPost('smtp_id') ? (int) $this->request->getPost('smtp_id') : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    }
}
