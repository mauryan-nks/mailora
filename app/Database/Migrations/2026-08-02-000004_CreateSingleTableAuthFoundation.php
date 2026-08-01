<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSingleTableAuthFoundation extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'uuid' => ['type'=>'CHAR','constraint'=>36,'null'=>true,'after'=>'id'],
            'account_level' => ['type'=>'TINYINT','unsigned'=>true,'default'=>5,'after'=>'uuid'],
            'account_type' => ['type'=>'VARCHAR','constraint'=>40,'default'=>'customer','after'=>'account_level'],
            'workspace_id' => ['type'=>'INT','unsigned'=>true,'null'=>true,'after'=>'reseller_id'],
            'workspace_role' => ['type'=>'VARCHAR','constraint'=>50,'null'=>true,'after'=>'workspace_id'],
            'first_name' => ['type'=>'VARCHAR','constraint'=>100,'null'=>true,'after'=>'company_name'],
            'last_name' => ['type'=>'VARCHAR','constraint'=>100,'null'=>true,'after'=>'first_name'],
            'email' => ['type'=>'VARCHAR','constraint'=>190,'null'=>true,'after'=>'last_name'],
            'phone' => ['type'=>'VARCHAR','constraint'=>30,'null'=>true,'after'=>'email'],
            'password_hash' => ['type'=>'VARCHAR','constraint'=>255,'null'=>true,'after'=>'phone'],
            'avatar' => ['type'=>'VARCHAR','constraint'=>500,'null'=>true,'after'=>'password_hash'],
            'timezone' => ['type'=>'VARCHAR','constraint'=>100,'default'=>'Asia/Kolkata','after'=>'avatar'],
            'locale' => ['type'=>'VARCHAR','constraint'=>20,'default'=>'en','after'=>'timezone'],
            'permissions' => ['type'=>'LONGTEXT','null'=>true,'after'=>'locale'],
            'permission_overrides' => ['type'=>'LONGTEXT','null'=>true,'after'=>'permissions'],
            'assigned_workspace_ids' => ['type'=>'LONGTEXT','null'=>true,'after'=>'permission_overrides'],
            'must_change_password' => ['type'=>'TINYINT','constraint'=>1,'default'=>0,'after'=>'assigned_workspace_ids'],
            'two_factor_enabled' => ['type'=>'TINYINT','constraint'=>1,'default'=>0,'after'=>'must_change_password'],
            'two_factor_secret_encrypted' => ['type'=>'TEXT','null'=>true,'after'=>'two_factor_enabled'],
            'email_verified_at' => ['type'=>'DATETIME','null'=>true,'after'=>'two_factor_secret_encrypted'],
            'last_login_at' => ['type'=>'DATETIME','null'=>true,'after'=>'email_verified_at'],
            'last_login_ip' => ['type'=>'VARCHAR','constraint'=>50,'null'=>true,'after'=>'last_login_at'],
            'session_version' => ['type'=>'INT','unsigned'=>true,'default'=>1,'after'=>'last_login_ip'],
            'created_by' => ['type'=>'INT','unsigned'=>true,'null'=>true,'after'=>'session_version'],
        ]);

        $this->db->query("UPDATE users u JOIN auth_identities i ON i.user_id=u.id AND i.type='email_password' SET u.email=i.secret,u.password_hash=i.secret2,u.first_name=COALESCE(u.username,'User'),u.uuid=UUID(),u.account_type=CASE u.user_type WHEN 'platform_admin' THEN 'platform_admin' WHEN 'admin_team' THEN 'platform_team' WHEN 'reseller' THEN 'reseller' WHEN 'reseller_team' THEN 'reseller_team' ELSE 'customer' END,u.account_level=CASE u.user_type WHEN 'platform_admin' THEN 1 WHEN 'admin_team' THEN 2 WHEN 'reseller' THEN 3 WHEN 'reseller_team' THEN 4 ELSE 5 END,u.status=CASE WHEN u.active=1 THEN 'active' ELSE 'disabled' END,u.email_verified_at=COALESCE(u.created_at,NOW())");
        $this->db->query("UPDATE users u LEFT JOIN workspace_members wm ON wm.user_id=u.id SET u.workspace_id=wm.workspace_id,u.workspace_role=COALESCE(wm.role,'owner') WHERE u.account_level=5");
        $this->db->query("UPDATE users SET uuid=UUID() WHERE uuid IS NULL");
        $this->forge->addUniqueKey('uuid'); $this->forge->addUniqueKey('email'); $this->forge->addKey('account_level'); $this->forge->addKey('account_type'); $this->forge->addKey('workspace_id'); $this->forge->processIndexes('users');

        $this->forge->addColumn('workspaces', [
            'uuid'=>['type'=>'CHAR','constraint'=>36,'null'=>true,'after'=>'id'],
            'reseller_id'=>['type'=>'INT','unsigned'=>true,'null'=>true,'after'=>'uuid'],
            'owner_user_id'=>['type'=>'INT','unsigned'=>true,'null'=>true,'after'=>'reseller_id'],
            'company_name'=>['type'=>'VARCHAR','constraint'=>190,'null'=>true,'after'=>'owner_user_id'],
            'contact_limit'=>['type'=>'BIGINT','unsigned'=>true,'default'=>1000],
            'monthly_email_limit'=>['type'=>'BIGINT','unsigned'=>true,'default'=>10000],
            'team_member_limit'=>['type'=>'INT','unsigned'=>true,'default'=>3],
            'automation_limit'=>['type'=>'INT','unsigned'=>true,'default'=>5],
            'template_limit'=>['type'=>'INT','unsigned'=>true,'default'=>100],
            'form_limit'=>['type'=>'INT','unsigned'=>true,'default'=>5],
            'landing_page_limit'=>['type'=>'INT','unsigned'=>true,'default'=>5],
            'api_key_limit'=>['type'=>'INT','unsigned'=>true,'default'=>2],
            'contacts_used'=>['type'=>'BIGINT','unsigned'=>true,'default'=>0],
            'emails_sent_current_month'=>['type'=>'BIGINT','unsigned'=>true,'default'=>0],
            'require_campaign_approval'=>['type'=>'TINYINT','constraint'=>1,'default'=>0],
            'status'=>['type'=>'VARCHAR','constraint'=>20,'default'=>'trial'],
            'trial_ends_at'=>['type'=>'DATETIME','null'=>true],
        ]);
        $this->db->query("UPDATE workspaces SET uuid=UUID(),company_name=name WHERE uuid IS NULL");
        $this->db->query("UPDATE workspaces w JOIN workspace_members wm ON wm.workspace_id=w.id AND wm.role='owner' SET w.owner_user_id=wm.user_id");
        $this->forge->addUniqueKey('uuid');$this->forge->addKey('reseller_id');$this->forge->processIndexes('workspaces');

        $this->createSecurityTables();
    }

    private function createSecurityTables(): void
    {
        $this->forge->addField(['id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],'user_id'=>['type'=>'INT','unsigned'=>true],'token_hash'=>['type'=>'CHAR','constraint'=>64],'expires_at'=>['type'=>'DATETIME'],'last_used_at'=>['type'=>'DATETIME','null'=>true],'created_at'=>['type'=>'DATETIME']]);$this->forge->addKey('id',true);$this->forge->addUniqueKey('token_hash');$this->forge->addKey('user_id');$this->forge->createTable('user_remember_tokens');
        $this->forge->addField(['id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],'email'=>['type'=>'VARCHAR','constraint'=>190],'ip_address'=>['type'=>'VARCHAR','constraint'=>50],'user_agent'=>['type'=>'VARCHAR','constraint'=>500,'null'=>true],'successful'=>['type'=>'TINYINT','constraint'=>1,'default'=>0],'attempted_at'=>['type'=>'DATETIME']]);$this->forge->addKey('id',true);$this->forge->addKey(['email','ip_address','attempted_at']);$this->forge->createTable('login_attempts');
        $this->forge->addField(['id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],'uuid'=>['type'=>'CHAR','constraint'=>36],'actor_user_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'reseller_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'workspace_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'event'=>['type'=>'VARCHAR','constraint'=>100],'subject_type'=>['type'=>'VARCHAR','constraint'=>100,'null'=>true],'subject_uuid'=>['type'=>'CHAR','constraint'=>36,'null'=>true],'metadata'=>['type'=>'LONGTEXT','null'=>true],'ip_address'=>['type'=>'VARCHAR','constraint'=>50,'null'=>true],'user_agent'=>['type'=>'VARCHAR','constraint'=>500,'null'=>true],'created_at'=>['type'=>'DATETIME']]);$this->forge->addKey('id',true);$this->forge->addUniqueKey('uuid');$this->forge->addKey(['workspace_id','created_at']);$this->forge->addKey(['actor_user_id','created_at']);$this->forge->createTable('audit_logs');
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],'name'=>['type'=>'VARCHAR','constraint'=>100],'scope'=>['type'=>'VARCHAR','constraint'=>30],'permissions'=>['type'=>'LONGTEXT'],'created_by'=>['type'=>'INT','unsigned'=>true,'null'=>true],'created_at'=>['type'=>'DATETIME'],'updated_at'=>['type'=>'DATETIME']]);$this->forge->addKey('id',true);$this->forge->createTable('role_presets');
        $this->forge->addField(['id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],'user_id'=>['type'=>'INT','unsigned'=>true],'permission_key'=>['type'=>'VARCHAR','constraint'=>150],'state'=>['type'=>'VARCHAR','constraint'=>10,'default'=>'inherit'],'changed_by'=>['type'=>'INT','unsigned'=>true,'null'=>true],'created_at'=>['type'=>'DATETIME'],'updated_at'=>['type'=>'DATETIME']]);$this->forge->addKey('id',true);$this->forge->addUniqueKey(['user_id','permission_key']);$this->forge->createTable('user_permission_overrides');
        $this->forge->addField(['id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],'user_id'=>['type'=>'INT','unsigned'=>true],'workspace_id'=>['type'=>'INT','unsigned'=>true],'assigned_by'=>['type'=>'INT','unsigned'=>true,'null'=>true],'created_at'=>['type'=>'DATETIME']]);$this->forge->addKey('id',true);$this->forge->addUniqueKey(['user_id','workspace_id']);$this->forge->createTable('user_workspace_assignments');
    }

    public function down(): void
    {
        foreach(['user_workspace_assignments','user_permission_overrides','role_presets','audit_logs','login_attempts','user_remember_tokens'] as $table)$this->forge->dropTable($table,true);
    }
}
