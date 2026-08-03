<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateMailoraTables extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('templates', [
            'editor_type' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'manual'],
            'source_url' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);

        $this->forge->addColumn('campaigns', [
            'template_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'smtp_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]);

        $this->forge->addColumn('automations', [
            'segment_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'smtp_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'trigger_event' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'flow_action' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'send_email'],
            'webhook_url' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'webhook_method' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'POST'],
            'webhook_payload' => ['type' => 'TEXT', 'null' => true],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('templates', ['editor_type', 'source_url']);
        $this->forge->dropColumn('campaigns', ['template_id']);
        $this->forge->dropColumn('automations', ['segment_id', 'smtp_id', 'trigger_event', 'flow_action', 'webhook_url', 'webhook_method', 'webhook_payload']);
    }
}
