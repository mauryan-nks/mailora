<?php
namespace App\Commands;

use App\Libraries\CampaignService;
use App\Models\CampaignModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ProcessCampaigns extends BaseCommand
{
    protected $group = 'Mailora';
    protected $name = 'campaigns:process';
    protected $description = 'Process scheduled campaigns for delivery.';

    public function run(array $params): void
    {
        $limit = max(1, (int) ($params[0] ?? 20));
        $now = date('Y-m-d H:i:s');
        $model = new CampaignModel();

        $rows = $model->where('workspace_id IS NOT NULL', null, false)
            ->where('status', 'scheduled')
            ->where('scheduled_at <=', $now)
            ->orderBy('scheduled_at', 'ASC')
            ->findAll($limit);

        $processed = 0;
        foreach ($rows as $campaign) {
            try {
                $workspaceId = (int) $campaign['workspace_id'];
                (new CampaignService($workspaceId))->sendCampaign((int) $campaign['id']);
                $processed++;
            } catch (\Throwable $e) {
                CLI::write('Campaign ' . $campaign['id'] . ' failed: ' . $e->getMessage(), 'red');
            }
        }

        CLI::write("Processed {$processed} campaign job(s).", 'green');
    }
}
