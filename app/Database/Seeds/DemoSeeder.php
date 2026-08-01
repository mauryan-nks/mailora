<?php
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('workspaces')->insert(['name'=>'Brightside Studio','slug'=>'brightside','timezone'=>'Asia/Kolkata','created_at'=>$now,'updated_at'=>$now]);
        foreach ([['VIP','#44B89D'],['Newsletter','#288DA5'],['Customers','#7E70BD']] as $tag) $this->db->table('tags')->insert(['workspace_id'=>1,'name'=>$tag[0],'color'=>$tag[1],'created_at'=>$now]);
        $contacts=[]; for($i=1;$i<=24;$i++) $contacts[]=['workspace_id'=>1,'email'=>"customer{$i}@example.com",'first_name'=>['Aarav','Meera','Ishaan','Diya'][$i%4],'last_name'=>'Subscriber','status'=>$i%13===0?'unsubscribed':'subscribed','source'=>$i%2?'csv':'form','created_at'=>$now,'updated_at'=>$now];
        $this->db->table('contacts')->insertBatch($contacts);
        $campaigns=[['Summer essentials are here','sent','2026-07-30 10:00:00'],['July product round-up','sent','2026-07-24 10:00:00'],['Weekend flash sale','scheduled','2026-08-04 11:00:00']];
        foreach($campaigns as $c) $this->db->table('campaigns')->insert(['workspace_id'=>1,'name'=>$c[0],'subject'=>$c[0],'from_name'=>'Brightside','from_email'=>'hello@example.com','content_html'=>'<h1>'.$c[0].'</h1><p>Your latest update is here.</p>','status'=>$c[1],'scheduled_at'=>$c[2],'timezone'=>'Asia/Kolkata','created_at'=>$now,'updated_at'=>$now]);
        foreach(['Ecommerce','Education','Healthcare','SaaS','Newsletter'] as $i=>$category) $this->db->table('templates')->insert(['workspace_id'=>1,'name'=>$category.' Starter','category'=>$category,'content_html'=>'<h1>'.$category.' campaign</h1><p>Start building your message.</p>','created_at'=>$now]);
        foreach([['Newsletter signup','embedded'],['Exit offer','popup'],['Free guide','landing_page']] as $i=>$form) $this->db->table('forms')->insert(['workspace_id'=>1,'name'=>$form[0],'form_type'=>$form[1],'slug'=>'form-'.($i+1),'headline'=>$form[0],'fields'=>'["email","first_name"]','status'=>'published','submissions'=>($i+1)*34,'created_at'=>$now,'updated_at'=>$now]);
        foreach([['Welcome new subscribers','signup'],['Birthday greeting','birthday'],['Customer follow-up','follow_up']] as $a) $this->db->table('automations')->insert(['workspace_id'=>1,'name'=>$a[0],'trigger_type'=>$a[1],'subject'=>$a[0],'content_html'=>'<p>Hello {{first_name}},</p>','status'=>'active','created_at'=>$now,'updated_at'=>$now]);
        $this->db->table('team_members')->insertBatch([['workspace_id'=>1,'name'=>'Olivia Martin','email'=>'olivia@example.com','role'=>'owner','status'=>'active','created_at'=>$now],['workspace_id'=>1,'name'=>'Alex Morgan','email'=>'alex@example.com','role'=>'editor','status'=>'active','created_at'=>$now]]);
    }
}
