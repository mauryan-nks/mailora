<?php
namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\AutomationModel;
use CodeIgniter\HTTP\ResponseInterface;

class Automations extends BaseController
{
    public function trigger(): ResponseInterface
    {
        $raw = trim((string) $this->request->getHeaderLine('X-API-Key'));
        $db = db_connect();
        $key = $raw === '' ? null : $db->table('contact_api_keys')->where(['secret_hash' => hash('sha256', $raw), 'status' => 'active'])->get()->getRowArray();

        if (! $key) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Invalid API key.']);
        }

        $workspace = $db->table('workspaces')->where('id', $key['workspace_id'])->get()->getRowArray();
        if (! $workspace) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Workspace not found.']);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $automationId = isset($payload['automation_id']) ? (int) $payload['automation_id'] : 0;
        $eventName = trim((string) ($payload['event'] ?? ''));

        if (! $automationId || $eventName === '') {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'automation_id and event are required.']);
        }

        $model = new AutomationModel();
        $automation = $model->where('workspace_id', $workspace['id'])->find($automationId);
        if (! $automation) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Automation not found.']);
        }

        if ($automation['status'] !== 'active') {
            return $this->response->setStatusCode(409)->setJSON(['error' => 'Automation is not active.']);
        }

        if ($automation['trigger_type'] !== 'api_event' || $automation['trigger_event'] !== $eventName) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'No automation matches this event.']);
        }

        $db->table('automations')->where('id', $automation['id'])->update(['updated_at' => date('Y-m-d H:i:s')]);

        return $this->response->setJSON([
            'success' => true,
            'automation_id' => $automation['id'],
            'trigger_event' => $eventName,
            'message' => 'Automation trigger received.',
        ]);
    }
}
