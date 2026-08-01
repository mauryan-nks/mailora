<?php
namespace App\Controllers;
class Analytics extends BaseController { public function index(): string { $rows=db_connect()->table('campaign_events ce')->select('ce.event_type, COUNT(*) total')->join('campaigns c','c.id=ce.campaign_id')->where('c.workspace_id',$this->workspaceId)->groupBy('ce.event_type')->get()->getResultArray(); return $this->page('simple/analytics',['title'=>'Analytics','active'=>'analytics','metrics'=>array_column($rows,'total','event_type')]); } }
