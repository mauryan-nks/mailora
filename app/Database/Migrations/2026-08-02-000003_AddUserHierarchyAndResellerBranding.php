<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserHierarchyAndResellerBranding extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'user_type' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'customer', 'after' => 'username'],
            'parent_user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'user_type'],
            'reseller_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'parent_user_id'],
            'company_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'reseller_id'],
        ]);

        $this->forge->addKey('user_type');
        $this->forge->addKey('parent_user_id');
        $this->forge->addKey('reseller_id');
        $this->forge->processIndexes('users');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'brand_name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'logo_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'favicon_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'primary_color' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => '#44B89D'],
            'secondary_color' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => '#288DA5'],
            'support_email' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('reseller_profiles');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'reseller_id' => ['type' => 'INT', 'unsigned' => true],
            'domain' => ['type' => 'VARCHAR', 'constraint' => 190],
            'verification_token' => ['type' => 'VARCHAR', 'constraint' => 64],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'verified_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('domain');
        $this->forge->addKey(['reseller_id', 'status']);
        $this->forge->addForeignKey('reseller_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('reseller_domains');
    }

    public function down(): void
    {
        $this->forge->dropTable('reseller_domains', true);
        $this->forge->dropTable('reseller_profiles', true);
        $this->forge->dropColumn('users', ['user_type', 'parent_user_id', 'reseller_id', 'company_name']);
    }
}
