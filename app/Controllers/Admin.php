<?php
namespace App\Controllers;

use App\Libraries\PlanQuotaService;
use App\Support\Uuid;
use Razorpay\Api\Api;
use Stripe\StripeClient;

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
    public function payments(): string
    {
        $gateways = db_connect()->table('payment_settings')->whereIn('gateway', ['stripe', 'razorpay'])->orderBy('gateway')->get()->getResultArray();

        return $this->page('admin/payments', [
            'title' => 'Payment Settings',
            'active' => 'admin-payments',
            'gateways' => $gateways,
        ]);
    }

    public function savePayment()
    {
        $gateway = (string) $this->request->getPost('gateway');
        if (! in_array($gateway, ['stripe', 'razorpay'], true)) {
            return redirect()->back()->with('error', 'Unsupported payment gateway.');
        }

        $mode = $this->request->getPost('mode') === 'live' ? 'live' : 'test';
        $publicKey = trim((string) $this->request->getPost('public_key'));
        $secretKey = trim((string) $this->request->getPost('secret_key'));
        $webhookSecret = trim((string) $this->request->getPost('webhook_secret'));
        $currency = strtoupper((string) ($this->request->getPost('currency') ?: 'USD'));
        $enabled = $this->request->getPost('enabled') ? 1 : 0;

        $db = db_connect();
        $old = $db->table('payment_settings')->where('gateway', $gateway)->get()->getRowArray();
        $existingSecret = null;
        $existingWebhookSecret = null;

        if ($old) {
            try {
                $existingSecret = $old['secret_encrypted'] ? service('encrypter')->decrypt($old['secret_encrypted']) : null;
            } catch (\Throwable $e) {
                $existingSecret = null;
            }

            try {
                $existingWebhookSecret = $old['webhook_secret_encrypted'] ? service('encrypter')->decrypt($old['webhook_secret_encrypted']) : null;
            } catch (\Throwable $e) {
                $existingWebhookSecret = null;
            }
        }

        if ($secretKey === '') {
            $secretKey = $existingSecret;
        }

        if ($webhookSecret === '') {
            $webhookSecret = $existingWebhookSecret;
        }

        if ($gateway === 'stripe') {
            if ($secretKey === '') {
                return redirect()->back()->withInput()->with('error', 'Stripe secret key is required.');
            }
        }

        if ($gateway === 'razorpay') {
            if ($publicKey === '' || $secretKey === '') {
                return redirect()->back()->withInput()->with('error', 'Razorpay key ID and secret are required.');
            }
        }

        $validationError = $this->validatePaymentCredentials($gateway, $mode, $publicKey, $secretKey);
        if ($validationError !== null) {
            return redirect()->back()->withInput()->with('error', $validationError);
        }

        $data = [
            'gateway' => $gateway,
            'enabled' => $enabled,
            'mode' => $mode,
            'public_key' => $publicKey ?: null,
            'secret_encrypted' => $secretKey ? service('encrypter')->encrypt($secretKey) : null,
            'webhook_secret_encrypted' => $webhookSecret ? service('encrypter')->encrypt($webhookSecret) : null,
            'currency' => $currency,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($old) {
            $db->table('payment_settings')->where('id', $old['id'])->update($data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $db->table('payment_settings')->insert($data);
        }

        return redirect()->back()->with('success', 'Payment settings saved.');
    }

    private function validatePaymentCredentials(string $gateway, string $mode, string $publicKey, string $secretKey): ?string
    {
        try {
            if ($gateway === 'stripe') {
                $client = new \Stripe\StripeClient($secretKey);
                $account = $client->accounts->retrieve();
                if (empty($account->id)) {
                    return 'Stripe credentials could not be verified.';
                }
            }

            if ($gateway === 'razorpay') {
                $api = new \Razorpay\Api\Api($publicKey, $secretKey);
                $api->payment->all(['count' => 1]);
            }
        } catch (\Throwable $e) {
            return $e->getMessage();
        }

        return null;
    }

    public function workers(): string
    {
        return $this->page('admin/workers', [
            'title' => 'Worker Setup',
            'active' => 'admin-workers',
            'workerStatus' => $this->getWorkerStatus(),
        ]);
    }

    public function verifyWorker()
    {
        $status = $this->getWorkerStatus();
        $errors = [];

        if (! is_executable(PHP_BINARY)) {
            $errors[] = 'PHP CLI does not appear executable at ' . PHP_BINARY . '.';
        }

        if (! $status['spark_exists']) {
            $errors[] = 'The spark CLI file is missing from the project root.';
        }

        if (! $status['logs_writable']) {
            $errors[] = 'The writable/logs directory is not writable by the web server.';
        }

        if (function_exists('shell_exec') && $status['spark_exists']) {
            $helpOutput = @shell_exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(FCPATH . 'spark') . ' --help 2>&1');
            if (! is_string($helpOutput) || trim($helpOutput) === '') {
                $errors[] = 'Unable to execute spark from web server context. Check permissions and PHP CLI availability.';
            }
        }

        if (! empty($errors)) {
            return redirect()->back()->with('error', implode(' ', $errors));
        }

        return redirect()->back()->with('success', 'Worker environment verified successfully.');
    }

    public function verifyCron()
    {
        if (! function_exists('shell_exec')) {
            return redirect()->back()->with('error', 'Cron verification is not available on this server because shell_exec is disabled.');
        }

        $expected = '* * * * * cd ' . escapeshellarg(FCPATH) . ' && ' . escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(FCPATH . 'spark') . ' campaigns:process >> ' . escapeshellarg(WRITEPATH . 'logs/campaign-worker.log') . ' 2>&1';
        $crontab = @shell_exec('crontab -l 2>&1');

        if (! is_string($crontab) || trim($crontab) === '') {
            return redirect()->back()->with('error', 'Unable to read the active crontab. Confirm the cron entry manually for the web server user.');
        }

        if (str_contains($crontab, 'campaigns:process') || str_contains($crontab, 'campaign:process')) {
            return redirect()->back()->with('success', 'Cron entry appears present. Verify the task runs once per minute as configured.');
        }

        return redirect()->back()->with('error', 'Cron entry not found. Add the recommended worker command to the server crontab.');
    }

    public function runWorker()
    {
        if (! function_exists('shell_exec')) {
            return redirect()->back()->with('error', 'Manual worker execution is not available because shell_exec is disabled.');
        }

        $command = 'cd ' . escapeshellarg(FCPATH) . ' && ' . escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(FCPATH . 'spark') . ' campaigns:process 1 2>&1';
        $output = @shell_exec($command);
        $message = 'Manual worker run completed.';

        if (! is_string($output)) {
            $output = 'No output was returned from the command.';
        }

        return redirect()->back()->with('success', $message)->with('worker_output', trim($output));
    }

    private function getWorkerStatus(): array
    {
        $logFile = WRITEPATH . 'logs/campaign-worker.log';
        $hasLog = file_exists($logFile);
        $lastRun = $hasLog ? date('M d, Y H:i:s', filemtime($logFile)) : 'No worker log found';
        $age = $hasLog ? (int) ((time() - filemtime($logFile)) / 60) : null;

        return [
            'last_run' => $lastRun,
            'last_run_age_minutes' => $age,
            'logs_writable' => is_writable(WRITEPATH . 'logs'),
            'php_binary' => PHP_BINARY,
            'spark_exists' => file_exists(FCPATH . 'spark'),
            'log_path' => $logFile,
        ];
    }
}
