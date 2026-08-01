<?php
namespace App\Libraries;
class PlanQuotaService
{
    public const LIMIT_FIELDS=['max_customers','max_team_members','max_domains','max_contacts','daily_email_limit','monthly_email_limit','max_smtp_accounts','max_api_keys'];
    public function limitReached(?int$limit,int$used):bool{return $limit!==null&&$used>=$limit;}
    public function planFor(array$user):?array{if(empty($user['plan_id']))return null;return db_connect()->table('plans')->where(['id'=>$user['plan_id'],'is_active'=>1])->get()->getRowArray();}
    public function assertCanCreate(array$actor,string$type):void
    {
        if(!in_array($actor['account_type'],['reseller','reseller_team'],true))return;$db=db_connect();$ownerId=$actor['account_type']==='reseller'?(int)$actor['id']:(int)$actor['reseller_id'];$owner=$db->table('users')->where('id',$ownerId)->get()->getRowArray();$plan=$owner?$this->planFor($owner):null;if(!$plan)throw new \DomainException('A reseller package must be assigned before creating accounts.');
        if($type==='customer'){$used=$db->table('users')->where(['reseller_id'=>$ownerId,'account_type'=>'customer','deleted_at'=>null])->countAllResults();if($this->limitReached($plan['max_customers']===null?null:(int)$plan['max_customers'],$used))throw new \DomainException('Your reseller customer limit has been reached.');}
        if($type==='reseller_team'){$used=$db->table('users')->where(['reseller_id'=>$ownerId,'account_type'=>'reseller_team','deleted_at'=>null])->countAllResults();if($this->limitReached($plan['max_team_members']===null?null:(int)$plan['max_team_members'],$used))throw new \DomainException('Your reseller team-member limit has been reached.');}
    }
    public function applyCustomerPlan(int$workspaceId,array$plan):void{db_connect()->table('workspaces')->where('id',$workspaceId)->update(['plan_id'=>$plan['id'],'contact_limit'=>$plan['max_contacts'],'team_member_limit'=>$plan['max_team_members'],'monthly_email_limit'=>$plan['monthly_email_limit'],'daily_email_limit'=>$plan['daily_email_limit'],'domain_limit'=>$plan['max_domains'],'api_key_limit'=>$plan['max_api_keys'],'updated_at'=>date('Y-m-d H:i:s')]);}
}
