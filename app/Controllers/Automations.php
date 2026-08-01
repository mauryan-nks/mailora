<?php
namespace App\Controllers;
class Automations extends BaseController { public function index(): string { return $this->page('simple/cards',['title'=>'Automation','active'=>'automations','subtitle'=>'Welcome, follow-up and birthday journeys','items'=>db_connect()->table('automations')->where('workspace_id',$this->workspaceId)->get()->getResultArray()]); } }
