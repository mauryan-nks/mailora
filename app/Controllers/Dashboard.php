<?php
namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $db=db_connect();
        $contacts=$db->table('contacts')->where('workspace_id',$this->workspaceId)->where('deleted_at',null)->countAllResults();
        $campaigns=$db->table('campaigns')->where('workspace_id',$this->workspaceId)->orderBy('id','DESC')->limit(5)->get()->getResultArray();
        $events=[]; foreach(['delivered','open','click','bounce','spam','unsubscribe'] as $type) $events[$type]=$db->table('campaign_events ce')->join('campaigns c','c.id=ce.campaign_id')->where('c.workspace_id',$this->workspaceId)->where('ce.event_type',$type)->countAllResults();
        return $this->page('dashboard/index',['title'=>'Overview','active'=>'dashboard','contacts'=>$contacts,'campaigns'=>$campaigns,'events'=>$events]);
    }
}
