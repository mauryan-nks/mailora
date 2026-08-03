<?php
namespace App\Commands;

use App\Libraries\CampaignService;
use App\Models\AutomationModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ProcessAutomations extends BaseCommand
{
    protected $group = 'Mailora';
    protected $name = 'automations:process';
    protected $description = 'Process due automation workflows.';

    public function run(array $params): void
    {
        $limit = max(1, (int) ($params[0] ?? 20));
        $model = new AutomationModel();
        $now = date('Y-m-d H:i:s');
        $rows = $model->where('status', 'active')
            ->where('trigger_type', 'follow_up')
            ->where('DATE_ADD(created_at, INTERVAL delay_minutes MINUTE) <=', $now)
            ->orderBy('id', 'ASC')
            ->findAll($limit);

        $processed = 0;
        foreach ($rows as $automation) {
            $workspaceId = (int) $automation['workspace_id'];
            try {
                $campaignService = new CampaignService($workspaceId, $automation['smtp_id'] ? (int) $automation['smtp_id'] : null);
                $campaignData = [
                    'workspace_id' => $workspaceId,
                    'name' => 'Automation: ' . $automation['name'],
                    'subject' => $automation['subject'],
                    'preview_text' => substr(strip_tags($automation['content_html']), 0, 120),
                    'from_name' => 'Automated Sender',
                    'from_email' => 'no-reply@' . parse_url(base_url(), PHP_URL_HOST),
                    'content_html' => $automation['content_html'],
                    'content_text' => strip_tags($automation['content_html']),
                    'editor_type' => 'html',
                    'status' => 'sending',
                    'scheduled_at' => null,
                    'timezone' => 'UTC',
                    'segment_id' => $automation['segment_id'] ? (int) $automation['segment_id'] : null,
                ];

                $db = db_connect();
                $campaignId = $db->table('campaigns')->insert($campaignData) ? $db->insertID() : null;
                if (! $campaignId) {
                    CLI::write('Failed to create campaign for automation ' . $automation['id'], 'yellow');
                    continue;
                }

                $campaignService->sendCampaign($campaignId);
                $processed++;
            } catch (\Throwable $e) {
                CLI::write('Automation ' . $automation['id'] . ' failed: ' . $e->getMessage(), 'red');
            }
        }

        CLI::write("Processed {$processed} automation job(s).", 'green');
    }
}
