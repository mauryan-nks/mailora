<?php

namespace App\Models;

use CodeIgniter\Model;

class AutomationModel extends Model
{
    protected $table = 'automations';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'workspace_id',
        'name',
        'trigger_type',
        'trigger_event',
        'flow_action',
        'delay_minutes',
        'subject',
        'content_html',
        'webhook_url',
        'webhook_method',
        'webhook_payload',
        'status',
        'segment_id',
        'smtp_id',
        'created_at',
        'updated_at',
    ];
}
