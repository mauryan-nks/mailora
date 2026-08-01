<?php

namespace App\Controllers;

class Reseller extends BaseController
{
    public function branding(): string
    {
        $resellerId=$this->resellerId(); $db=db_connect();
        return $this->page('reseller/branding',['title'=>'White label','active'=>'branding','profile'=>$db->table('reseller_profiles')->where('user_id',$resellerId)->get()->getRowArray(),'domains'=>$db->table('reseller_domains')->where('reseller_id',$resellerId)->get()->getResultArray()]);
    }
    public function saveBranding()
    {
        $id=$this->resellerId(); $db=db_connect(); $data=['brand_name'=>$this->request->getPost('brand_name'),'primary_color'=>$this->request->getPost('primary_color'),'secondary_color'=>$this->request->getPost('secondary_color'),'support_email'=>$this->request->getPost('support_email'),'updated_at'=>date('Y-m-d H:i:s')];
        foreach(['logo'=>'logo_path','favicon'=>'favicon_path'] as $field=>$column){$file=$this->request->getFile($field);if($file&&$file->isValid()&&!$file->hasMoved()){if(!in_array(strtolower($file->getExtension()),['png','jpg','jpeg','webp','ico','svg'],true))return redirect()->back()->with('error','Brand images must be PNG, JPG, WebP, ICO or SVG.');$name=$file->getRandomName();$file->move(FCPATH.'uploads/branding',$name);$data[$column]='uploads/branding/'.$name;}}
        $existing=$db->table('reseller_profiles')->where('user_id',$id)->countAllResults(); if($existing)$db->table('reseller_profiles')->where('user_id',$id)->update($data);else{$data['user_id']=$id;$data['created_at']=date('Y-m-d H:i:s');$db->table('reseller_profiles')->insert($data);}
        return redirect()->back()->with('success','White-label branding saved.');
    }
    public function saveDomain()
    {
        $id=$this->resellerId();$domain=strtolower(trim((string)$this->request->getPost('domain')));$domain=preg_replace('#^https?://#','',$domain);$domain=rtrim(explode('/',$domain)[0],'.');
        if(!filter_var('https://'.$domain,FILTER_VALIDATE_URL)||!str_contains($domain,'.'))return redirect()->back()->with('error','Enter a valid hostname without a path.');
        try{db_connect()->table('reseller_domains')->insert(['reseller_id'=>$id,'domain'=>$domain,'verification_token'=>bin2hex(random_bytes(24)),'status'=>'pending','created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);}catch(\Throwable){return redirect()->back()->with('error','That domain is already registered.');}
        return redirect()->back()->with('success','Domain added. Point its CNAME to the main Mailora domain, then request verification.');
    }
    public function verifyDomain(int $domainId)
    {
        $resellerId=$this->resellerId();$db=db_connect();$domain=$db->table('reseller_domains')->where(['id'=>$domainId,'reseller_id'=>$resellerId])->get()->getRowArray();
        if(!$domain)throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $expected=strtolower(rtrim((string)env('app.cnameTarget',''),'.'));
        if($expected==='')return redirect()->back()->with('error','Set app.cnameTarget in .env before verifying reseller domains.');
        $records=dns_get_record($domain['domain'],DNS_CNAME);$verified=false;foreach($records?:[] as $record){if(strtolower(rtrim((string)($record['target']??''),'.'))===$expected){$verified=true;break;}}
        if(!$verified)return redirect()->back()->with('error',"CNAME does not point to {$expected} yet. DNS propagation may take time.");
        $db->table('reseller_domains')->where('id',$domainId)->update(['status'=>'verified','verified_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
        return redirect()->back()->with('success','Domain verified. Reseller branding is now active on that hostname.');
    }
    private function resellerId(): int { $u=current_user();if($u['account_type']==='reseller')return(int)$u['id'];if($u['account_type']==='reseller_team'&&$u['reseller_id'])return(int)$u['reseller_id'];throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(); }
}
