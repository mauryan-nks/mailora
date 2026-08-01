<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMailoraTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true], 'name'=>['type'=>'VARCHAR','constraint'=>120], 'slug'=>['type'=>'VARCHAR','constraint'=>120,'unique'=>true], 'timezone'=>['type'=>'VARCHAR','constraint'=>64,'default'=>'Asia/Kolkata'], 'logo'=>['type'=>'VARCHAR','constraint'=>255,'null'=>true], 'brand_color'=>['type'=>'VARCHAR','constraint'=>10,'default'=>'#44B89D'], 'custom_domain'=>['type'=>'VARCHAR','constraint'=>190,'null'=>true], 'created_at'=>['type'=>'DATETIME','null'=>true], 'updated_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id', true); $this->forge->createTable('workspaces');

        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true], 'workspace_id'=>['type'=>'INT','unsigned'=>true], 'email'=>['type'=>'VARCHAR','constraint'=>190], 'first_name'=>['type'=>'VARCHAR','constraint'=>100,'null'=>true], 'last_name'=>['type'=>'VARCHAR','constraint'=>100,'null'=>true], 'phone'=>['type'=>'VARCHAR','constraint'=>40,'null'=>true], 'birthday'=>['type'=>'DATE','null'=>true], 'status'=>['type'=>'VARCHAR','constraint'=>24,'default'=>'subscribed'], 'source'=>['type'=>'VARCHAR','constraint'=>50,'default'=>'manual'], 'custom_fields'=>['type'=>'TEXT','null'=>true], 'created_at'=>['type'=>'DATETIME','null'=>true], 'updated_at'=>['type'=>'DATETIME','null'=>true], 'deleted_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id', true); $this->forge->addUniqueKey(['workspace_id','email']); $this->forge->addKey(['workspace_id','status']); $this->forge->createTable('contacts');

        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true], 'workspace_id'=>['type'=>'INT','unsigned'=>true], 'name'=>['type'=>'VARCHAR','constraint'=>100], 'color'=>['type'=>'VARCHAR','constraint'=>10,'default'=>'#44B89D'], 'created_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id', true); $this->forge->createTable('tags');
        $this->forge->addField(['contact_id'=>['type'=>'INT','unsigned'=>true], 'tag_id'=>['type'=>'INT','unsigned'=>true]]); $this->forge->addKey(['contact_id','tag_id'], true); $this->forge->createTable('contact_tags');

        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true], 'workspace_id'=>['type'=>'INT','unsigned'=>true], 'name'=>['type'=>'VARCHAR','constraint'=>120], 'rules'=>['type'=>'TEXT'], 'created_at'=>['type'=>'DATETIME','null'=>true], 'updated_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id', true); $this->forge->createTable('segments');

        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true], 'workspace_id'=>['type'=>'INT','unsigned'=>true], 'name'=>['type'=>'VARCHAR','constraint'=>160], 'subject'=>['type'=>'VARCHAR','constraint'=>190], 'preview_text'=>['type'=>'VARCHAR','constraint'=>255,'null'=>true], 'from_name'=>['type'=>'VARCHAR','constraint'=>120], 'from_email'=>['type'=>'VARCHAR','constraint'=>190], 'content_html'=>['type'=>'TEXT','null'=>true], 'content_text'=>['type'=>'TEXT','null'=>true], 'editor_type'=>['type'=>'VARCHAR','constraint'=>20,'default'=>'richtext'], 'status'=>['type'=>'VARCHAR','constraint'=>30,'default'=>'draft'], 'segment_id'=>['type'=>'INT','unsigned'=>true,'null'=>true], 'scheduled_at'=>['type'=>'DATETIME','null'=>true], 'timezone'=>['type'=>'VARCHAR','constraint'=>64,'default'=>'Asia/Kolkata'], 'sent_at'=>['type'=>'DATETIME','null'=>true], 'created_at'=>['type'=>'DATETIME','null'=>true], 'updated_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id', true); $this->forge->addKey(['workspace_id','status']); $this->forge->createTable('campaigns');

        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true], 'campaign_id'=>['type'=>'INT','unsigned'=>true], 'contact_id'=>['type'=>'INT','unsigned'=>true,'null'=>true], 'event_type'=>['type'=>'VARCHAR','constraint'=>30], 'url'=>['type'=>'TEXT','null'=>true], 'occurred_at'=>['type'=>'DATETIME']]);
        $this->forge->addKey('id', true); $this->forge->addKey(['campaign_id','event_type']); $this->forge->createTable('campaign_events');

        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true], 'workspace_id'=>['type'=>'INT','unsigned'=>true], 'name'=>['type'=>'VARCHAR','constraint'=>150], 'category'=>['type'=>'VARCHAR','constraint'=>60], 'thumbnail'=>['type'=>'VARCHAR','constraint'=>255,'null'=>true], 'content_html'=>['type'=>'TEXT'], 'created_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id', true); $this->forge->createTable('templates');

        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true], 'workspace_id'=>['type'=>'INT','unsigned'=>true], 'name'=>['type'=>'VARCHAR','constraint'=>150], 'form_type'=>['type'=>'VARCHAR','constraint'=>30,'default'=>'embedded'], 'slug'=>['type'=>'VARCHAR','constraint'=>150], 'headline'=>['type'=>'VARCHAR','constraint'=>190], 'fields'=>['type'=>'TEXT'], 'status'=>['type'=>'VARCHAR','constraint'=>20,'default'=>'draft'], 'submissions'=>['type'=>'INT','default'=>0], 'created_at'=>['type'=>'DATETIME','null'=>true], 'updated_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id', true); $this->forge->addUniqueKey(['workspace_id','slug']); $this->forge->createTable('forms');

        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true], 'workspace_id'=>['type'=>'INT','unsigned'=>true], 'name'=>['type'=>'VARCHAR','constraint'=>150], 'trigger_type'=>['type'=>'VARCHAR','constraint'=>40], 'delay_minutes'=>['type'=>'INT','default'=>0], 'subject'=>['type'=>'VARCHAR','constraint'=>190], 'content_html'=>['type'=>'TEXT'], 'status'=>['type'=>'VARCHAR','constraint'=>20,'default'=>'paused'], 'created_at'=>['type'=>'DATETIME','null'=>true], 'updated_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id', true); $this->forge->createTable('automations');

        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true], 'workspace_id'=>['type'=>'INT','unsigned'=>true], 'provider'=>['type'=>'VARCHAR','constraint'=>30], 'host'=>['type'=>'VARCHAR','constraint'=>190,'null'=>true], 'port'=>['type'=>'INT','null'=>true], 'username'=>['type'=>'VARCHAR','constraint'=>190,'null'=>true], 'encrypted_password'=>['type'=>'TEXT','null'=>true], 'encryption'=>['type'=>'VARCHAR','constraint'=>10,'default'=>'tls'], 'from_email'=>['type'=>'VARCHAR','constraint'=>190], 'from_name'=>['type'=>'VARCHAR','constraint'=>120], 'is_active'=>['type'=>'TINYINT','default'=>1], 'created_at'=>['type'=>'DATETIME','null'=>true], 'updated_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id', true); $this->forge->createTable('smtp_accounts');

        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true], 'workspace_id'=>['type'=>'INT','unsigned'=>true], 'name'=>['type'=>'VARCHAR','constraint'=>120], 'email'=>['type'=>'VARCHAR','constraint'=>190], 'role'=>['type'=>'VARCHAR','constraint'=>30,'default'=>'viewer'], 'status'=>['type'=>'VARCHAR','constraint'=>20,'default'=>'invited'], 'created_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id', true); $this->forge->addUniqueKey(['workspace_id','email']); $this->forge->createTable('team_members');
    }

    public function down(): void
    {
        foreach (['team_members','smtp_accounts','automations','forms','templates','campaign_events','campaigns','segments','contact_tags','tags','contacts','workspaces'] as $table) $this->forge->dropTable($table, true);
    }
}
