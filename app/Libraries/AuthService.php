<?php
namespace App\Libraries;
use App\Models\UserModel;

class AuthService
{
    private ?array $cached=null;
    public function user(): ?array { if($this->cached!==null)return $this->cached;$id=session('auth_user_id');if(!$id)return null;$u=(new UserModel())->find($id);if(!$u||$u['status']!=='active'||(int)$u['session_version']!==(int)session('auth_session_version')){$this->logout(false);return null;}return $this->cached=$u; }
    public function id(): ?int { return ($u=$this->user())?(int)$u['id']:null; }
    public function loggedIn(): bool { return $this->user()!==null; }
    public function attempt(string $email,string $password): bool
    {
        $email=strtolower(trim($email));$request=service('request');$db=db_connect();$since=date('Y-m-d H:i:s',time()-900);$failures=$db->table('login_attempts')->where(['email'=>$email,'ip_address'=>$request->getIPAddress(),'successful'=>0])->where('attempted_at >=',$since)->countAllResults();if($failures>=5)return false;
        $user=(new UserModel())->where('email',$email)->first();$ok=$user&&$user['status']==='active'&&$user['password_hash']&&password_verify($password,$user['password_hash']);$db->table('login_attempts')->insert(['email'=>$email,'ip_address'=>$request->getIPAddress(),'user_agent'=>substr((string)$request->getUserAgent(),0,500),'successful'=>$ok?1:0,'attempted_at'=>date('Y-m-d H:i:s')]);
        if(!$ok){(new AuditLogService())->record('auth.login_failed',$user,['email'=>$email]);return false;}
        service('session')->regenerate(true);session()->set(['auth_user_id'=>$user['id'],'auth_session_version'=>$user['session_version']]);$this->cached=$user;(new UserModel())->update($user['id'],['last_login_at'=>date('Y-m-d H:i:s'),'last_login_ip'=>$request->getIPAddress()]);(new AuditLogService())->record('auth.login',$user);return true;
    }
    public function logout(bool $audit=true): void { $user=$this->cached;if($audit&&$user)(new AuditLogService())->record('auth.logout',$user);session()->remove(['auth_user_id','auth_session_version']);service('session')->regenerate(true);$this->cached=null; }
}
