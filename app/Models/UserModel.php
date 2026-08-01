<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\PlanQuotaService;

class UserModel extends Model
{
    protected $table='users'; protected $returnType='array'; protected $useTimestamps=true; protected $useSoftDeletes=true;
    protected $beforeInsert=['enforceResellerQuota'];
    protected $allowedFields=['uuid','parent_user_id','reseller_id','workspace_id','plan_id','account_level','account_type','workspace_role','username','first_name','last_name','email','phone','password_hash','avatar','timezone','locale','permissions','permission_overrides','assigned_workspace_ids','must_change_password','two_factor_enabled','two_factor_secret_encrypted','status','email_verified_at','last_login_at','last_login_ip','session_version','created_by','company_name','active'];
    protected $validationRules=['email'=>'required|valid_email|max_length[190]','account_level'=>'required|integer|greater_than_equal_to[1]|less_than_equal_to[5]','account_type'=>'required|in_list[platform_admin,platform_team,reseller,reseller_team,customer]'];
    protected function enforceResellerQuota(array$data):array
    {
        $row=$data['data']??[];if(!empty($row['reseller_id'])&&in_array($row['account_type']??'', ['customer','reseller_team'],true)){$owner=$this->db->table('users')->where('id',$row['reseller_id'])->get()->getRowArray();if($owner)(new PlanQuotaService())->assertCanCreate($owner,$row['account_type']);}return$data;
    }
}
