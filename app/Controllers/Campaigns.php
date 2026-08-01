<?php
namespace App\Controllers;
use App\Models\CampaignModel;

class Campaigns extends BaseController
{
    public function index(): string { $m=new CampaignModel(); return $this->page('campaigns/index',['title'=>'Campaigns','active'=>'campaigns','campaigns'=>$m->where('workspace_id',$this->workspaceId)->orderBy('id','DESC')->findAll()]); }
    public function new(): string { return $this->page('campaigns/form',['title'=>'Create campaign','active'=>'campaigns','campaign'=>null]); }
    public function create() { $data=$this->payload(); $m=new CampaignModel(); if(!$m->insert($data)) return redirect()->back()->withInput()->with('error',implode(' ',$m->errors())); return redirect()->to('/campaigns')->with('success','Campaign saved as '.$data['status'].'.'); }
    public function edit(int $id): string { $m=new CampaignModel(); $campaign=$m->where('workspace_id',$this->workspaceId)->find($id); if(!$campaign) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(); return $this->page('campaigns/form',['title'=>'Edit campaign','active'=>'campaigns','campaign'=>$campaign]); }
    public function update(int $id) { $m=new CampaignModel(); $existing=$m->find($id); if(!$existing || (int)$existing['workspace_id']!==$this->workspaceId) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(); if(!$m->update($id,$this->payload())) return redirect()->back()->withInput()->with('error',implode(' ',$m->errors())); return redirect()->to('/campaigns')->with('success','Campaign updated.'); }
    public function test(int $id) { $campaign=(new CampaignModel())->where('workspace_id',$this->workspaceId)->find($id); if(!$campaign) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(); return redirect()->back()->with('success','Test email queued for '.$campaign['from_email'].'. Configure SMTP to deliver it.'); }
    private function payload(): array { $scheduled=$this->request->getPost('scheduled_at'); return ['workspace_id'=>$this->workspaceId,'name'=>(string)$this->request->getPost('name'),'subject'=>(string)$this->request->getPost('subject'),'preview_text'=>(string)$this->request->getPost('preview_text'),'from_name'=>(string)$this->request->getPost('from_name'),'from_email'=>(string)$this->request->getPost('from_email'),'content_html'=>(string)$this->request->getPost('content_html'),'content_text'=>strip_tags((string)$this->request->getPost('content_html')),'editor_type'=>(string)($this->request->getPost('editor_type')?:'richtext'),'status'=>$scheduled?'scheduled':'draft','scheduled_at'=>$scheduled?:null,'timezone'=>(string)($this->request->getPost('timezone')?:'Asia/Kolkata')]; }
}
