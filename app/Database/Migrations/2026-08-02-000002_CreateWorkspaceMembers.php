<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWorkspaceMembers extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'workspace_id' => ['type' => 'INT', 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'role' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'viewer'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['workspace_id', 'user_id']);
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('workspace_id', 'workspaces', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('workspace_members');
    }

    public function down(): void
    {
        $this->forge->dropTable('workspace_members', true);
    }
}
