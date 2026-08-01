<?php
namespace App\Controllers;

use App\Libraries\PlanQuotaService;
use App\Support\Uuid;

class Admin extends BaseController
{
    public function settings(): string
    {
        $db=db_connect();
        return $this->page('admin/settings',['title'=>'Admin Settings','active'=>'admin-settings','stats'=>[
            'users'=>$db->table('users')->where('account_type','customer')->where('deleted_at',null)->countAllResults(),
            'resellers'=>$db->table('users')->where('account_type','reseller')->where('deleted_at',null)->countAllResults(),
            'plans'=>$db->table('plans')->countAllResults(),
        ]]);
    }

    public function users(string $type='customer'): string
    {
        $type=$type==='reseller'?'reseller':'customer';
        $rows=db_connect()->table('users u')->select('u.id,u.uuid,u.first_name,u.last_name,u.email,u.company_name,u.status,u.plan_id,p.name plan_name')->join('plans p','p.id=u.plan_id','left')->where('u.account_type',$type)->where('u.deleted_at',null)->orderBy('u.id','DESC')->get()->getResultArray();
        return $this->page('admin/users',['title'=>$type==='reseller'?'Resellers':'Users','active'=>$type==='reseller'?'admin-resellers':'admin-users','rows'=>$rows,'type'=>$type,'plans'=>db_connect()->table('plans')->where('audience_type',$type)->where('is_active',1)->orderBy('name')->get()->getResultArray()]);
    }

    public function assignPlan(string $uuid)
    {
        $db=db_connect();$user=$db->table('users')->where('uuid',$uuid)->get()->getRowArray();$planId=(int)$this->request->getPost('plan_id');
        if(!$user||!in_array($user['account_type'],['customer','reseller'],true))throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $plan=$db->table('plans')->where(['id'=>$planId,'audience_type'=>$user['account_type']])->get()->getRowArray();if(!$plan)return redirect()->back()->with('error','Choose a compatible plan.');
        $db->transStart();$db->table('users')->where('id',$user['id'])->update(['plan_id'=>$planId]);if($user['account_type']==='customer'&&$user['workspace_id'])(new PlanQuotaService())->applyCustomerPlan((int)$user['workspace_id'],$plan);$db->table('subscriptions')->where('user_id',$user['id'])->where('status','active')->update(['status'=>'replaced','ends_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);$db->table('subscriptions')->insert(['uuid'=>Uuid::v4(),'user_id'=>$user['id'],'workspace_id'=>$user['workspace_id']?:null,'plan_id'=>$planId,'status'=>'active','starts_at'=>date('Y-m-d H:i:s'),'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);$db->transComplete();
        return redirect()->back()->with('success','Plan assigned.');
    }

    public function plans(): string { return $this->page('admin/plans',['title'=>'Packages & Limits','active'=>'admin-plans','plans'=>db_connect()->table('plans')->orderBy('audience_type')->orderBy('price')->get()->getResultArray()]); }
    public function savePlan()
    {
        $rules=['name'=>'required|max_length[120]','audience_type'=>'required|in_list[customer,reseller]','price'=>'required|decimal','currency'=>'required|exact_length[3]','billing_cycle'=>'required|in_list[monthly,yearly,one_time]'];if(!$this->validateData($this->request->getPost(),$rules))return redirect()->back()->withInput()->with('error',implode(' ',$this->validator->getErrors()));
        $unlimited=(array)$this->request->getPost('unlimited');$data=['uuid'=>Uuid::v4(),'name'=>$this->request->getPost('name'),'audience_type'=>$this->request->getPost('audience_type'),'price'=>$this->request->getPost('price'),'currency'=>strtoupper((string)$this->request->getPost('currency')),'billing_cycle'=>$this->request->getPost('billing_cycle'),'is_active'=>1,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')];foreach(['max_customers','max_team_members','max_domains','max_contacts','daily_email_limit','monthly_email_limit','max_smtp_accounts','max_api_keys'] as$f)$data[$f]=in_array($f,$unlimited,true)?null:max(0,(int)$this->request->getPost($f));db_connect()->table('plans')->insert($data);return redirect()->back()->with('success','Package created.');
    }

    public function updatePlan(string$uuid)
    {
        $db=db_connect();$plan=$db->table('plans')->where('uuid',$uuid)->get()->getRowArray();if(!$plan)throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();$unlimited=(array)$this->request->getPost('unlimited');$data=['name'=>trim((string)$this->request->getPost('name'))?:$plan['name'],'price'=>max(0,(float)$this->request->getPost('price')),'is_active'=>$this->request->getPost('is_active')?1:0,'updated_at'=>date('Y-m-d H:i:s')];foreach(PlanQuotaService::LIMIT_FIELDS as$f)$data[$f]=in_array($f,$unlimited,true)?null:max(0,(int)$this->request->getPost($f));$db->table('plans')->where('id',$plan['id'])->update($data);return redirect()->back()->with('success','Package updated. Reassign it to refresh an existing customer workspace.');
    }
    public function changeStatus(string$uuid)
    {
        $db=db_connect();$user=$db->table('users')->where('uuid',$uuid)->get()->getRowArray();if(!$user||!in_array($user['account_type'],['customer','reseller'],true))throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();$active=$user['status']!=='active';$db->table('users')->where('id',$user['id'])->update(['status'=>$active?'active':'disabled','active'=>$active?1:0,'session_version'=>(int)$user['session_version']+1,'updated_at'=>date('Y-m-d H:i:s')]);return redirect()->back()->with('success',$active?'Account activated.':'Account disabled and active sessions invalidated.');
    }
    public function payments(): string { return $this->page('admin/payments',['title'=>'Payment Settings','active'=>'admin-payments','gateways'=>db_connect()->table('payment_settings')->orderBy('gateway')->get()->getResultArray()]); }
    public function savePayment()
    {
        $gateway=(string)$this->request->getPost('gateway');if(!in_array($gateway,['stripe','razorpay','paypal'],true))return redirect()->back()->with('error','Unsupported payment gateway.');$db=db_connect();$data=['gateway'=>$gateway,'enabled'=>$this->request->getPost('enabled')?1:0,'mode'=>$this->request->getPost('mode')==='live'?'live':'test','public_key'=>$this->request->getPost('public_key')?:null,'currency'=>strtoupper((string)($this->request->getPost('currency')?:'USD')),'updated_at'=>date('Y-m-d H:i:s')];$old=$db->table('payment_settings')->where('gateway',$gateway)->get()->getRowArray();if($old)$db->table('payment_settings')->where('id',$old['id'])->update($data);else$db->table('payment_settings')->insert($data+['created_at'=>date('Y-m-d H:i:s')]);return redirect()->back()->with('success','Payment settings saved. Secret keys must be configured securely in the server environment.');
    }
}
