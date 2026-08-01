<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class ActivatePlanLimits extends Migration
{
    public function up():void{$this->forge->modifyColumn('workspaces',['contact_limit'=>['type'=>'BIGINT','unsigned'=>true,'null'=>true],'monthly_email_limit'=>['type'=>'BIGINT','unsigned'=>true,'null'=>true],'team_member_limit'=>['type'=>'INT','unsigned'=>true,'null'=>true],'api_key_limit'=>['type'=>'INT','unsigned'=>true,'null'=>true]]);$this->forge->addColumn('workspaces',['daily_email_limit'=>['type'=>'BIGINT','unsigned'=>true,'null'=>true,'after'=>'monthly_email_limit'],'domain_limit'=>['type'=>'INT','unsigned'=>true,'null'=>true,'after'=>'daily_email_limit']]);}
    public function down():void{$this->forge->dropColumn('workspaces',['daily_email_limit','domain_limit']);}
}
