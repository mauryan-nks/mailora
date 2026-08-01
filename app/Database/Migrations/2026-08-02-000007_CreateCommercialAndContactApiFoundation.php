<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCommercialAndContactApiFoundation extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true], 'uuid'=>['type'=>'CHAR','constraint'=>36],
            'name'=>['type'=>'VARCHAR','constraint'=>120], 'audience_type'=>['type'=>'VARCHAR','constraint'=>20],
            'price'=>['type'=>'DECIMAL','constraint'=>'12,2','default'=>0], 'currency'=>['type'=>'CHAR','constraint'=>3,'default'=>'USD'],
            'billing_cycle'=>['type'=>'VARCHAR','constraint'=>20,'default'=>'monthly'], 'is_active'=>['type'=>'TINYINT','constraint'=>1,'default'=>1],
            'max_customers'=>['type'=>'INT','unsigned'=>true,'null'=>true], 'max_team_members'=>['type'=>'INT','unsigned'=>true,'null'=>true],
            'max_domains'=>['type'=>'INT','unsigned'=>true,'null'=>true], 'max_contacts'=>['type'=>'BIGINT','unsigned'=>true,'null'=>true],
            'daily_email_limit'=>['type'=>'BIGINT','unsigned'=>true,'null'=>true], 'monthly_email_limit'=>['type'=>'BIGINT','unsigned'=>true,'null'=>true],
            'max_smtp_accounts'=>['type'=>'INT','unsigned'=>true,'null'=>true], 'max_api_keys'=>['type'=>'INT','unsigned'=>true,'null'=>true],
            'features'=>['type'=>'LONGTEXT','null'=>true], 'created_at'=>['type'=>'DATETIME'], 'updated_at'=>['type'=>'DATETIME'],
        ]);
        $this->forge->addKey('id',true); $this->forge->addUniqueKey('uuid'); $this->forge->addKey(['audience_type','is_active']); $this->forge->createTable('plans');

        $this->forge->addColumn('users',['plan_id'=>['type'=>'INT','unsigned'=>true,'null'=>true,'after'=>'workspace_id']]);
        $this->forge->addColumn('workspaces',['plan_id'=>['type'=>'INT','unsigned'=>true,'null'=>true,'after'=>'owner_user_id']]);

        $this->forge->addField([
            'id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true], 'uuid'=>['type'=>'CHAR','constraint'=>36],
            'user_id'=>['type'=>'INT','unsigned'=>true,'null'=>true], 'workspace_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],
            'plan_id'=>['type'=>'INT','unsigned'=>true], 'status'=>['type'=>'VARCHAR','constraint'=>30,'default'=>'active'],
            'starts_at'=>['type'=>'DATETIME'], 'ends_at'=>['type'=>'DATETIME','null'=>true], 'created_at'=>['type'=>'DATETIME'], 'updated_at'=>['type'=>'DATETIME'],
        ]);
        $this->forge->addKey('id',true); $this->forge->addUniqueKey('uuid'); $this->forge->addKey(['user_id','status']); $this->forge->addKey(['workspace_id','status']); $this->forge->createTable('subscriptions');

        $this->forge->addField([
            'id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true], 'gateway'=>['type'=>'VARCHAR','constraint'=>50],
            'enabled'=>['type'=>'TINYINT','constraint'=>1,'default'=>0], 'mode'=>['type'=>'VARCHAR','constraint'=>20,'default'=>'test'],
            'public_key'=>['type'=>'TEXT','null'=>true], 'secret_encrypted'=>['type'=>'TEXT','null'=>true],
            'webhook_secret_encrypted'=>['type'=>'TEXT','null'=>true], 'currency'=>['type'=>'CHAR','constraint'=>3,'default'=>'USD'],
            'created_at'=>['type'=>'DATETIME'], 'updated_at'=>['type'=>'DATETIME'],
        ]);
        $this->forge->addKey('id',true); $this->forge->addUniqueKey('gateway'); $this->forge->createTable('payment_settings');

        $this->forge->addField([
            'id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true], 'uuid'=>['type'=>'CHAR','constraint'=>36],
            'workspace_id'=>['type'=>'INT','unsigned'=>true], 'name'=>['type'=>'VARCHAR','constraint'=>120],
            'key_prefix'=>['type'=>'VARCHAR','constraint'=>20], 'secret_hash'=>['type'=>'CHAR','constraint'=>64],
            'status'=>['type'=>'VARCHAR','constraint'=>20,'default'=>'active'], 'last_used_at'=>['type'=>'DATETIME','null'=>true],
            'created_by'=>['type'=>'INT','unsigned'=>true], 'created_at'=>['type'=>'DATETIME'], 'updated_at'=>['type'=>'DATETIME'],
        ]);
        $this->forge->addKey('id',true); $this->forge->addUniqueKey('uuid'); $this->forge->addUniqueKey('secret_hash'); $this->forge->addKey(['workspace_id','status']); $this->forge->createTable('contact_api_keys');
    }

    public function down(): void
    {
        foreach(['contact_api_keys','payment_settings','subscriptions','plans'] as $table)$this->forge->dropTable($table,true);
        $this->forge->dropColumn('workspaces','plan_id'); $this->forge->dropColumn('users','plan_id');
    }
}
