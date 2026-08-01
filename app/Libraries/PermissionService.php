<?php
namespace App\Libraries;
class PermissionService
{
    private const PRESETS=['platform_admin'=>['*'],'platform_team'=>['dashboard.view'],'reseller'=>['dashboard.*','contacts.*','campaigns.*','templates.*','automations.*','forms.*','analytics.*','team.*','smtp.*','white_label.*','reports.*','settings.*'],'reseller_team'=>['dashboard.view','contacts.view','campaigns.view','templates.view','analytics.view'],'customer'=>['dashboard.view','contacts.*','campaigns.*','templates.*','automations.*','forms.*','analytics.*','team.*','smtp.*','reports.*','settings.*']];
    public function allows(array $user,string $permission,?int $workspaceId=null):bool
    {
        if($workspaceId!==null&&!(new WorkspaceAccessService())->canAccess($user,$workspaceId))return false;if($user['account_type']==='platform_admin')return true;$overrides=db_connect()->table('user_permission_overrides')->where(['user_id'=>$user['id'],'permission_key'=>$permission])->get()->getRowArray();if($overrides&&$overrides['state']==='deny')return false;if($overrides&&$overrides['state']==='allow')return true;$json=json_decode((string)($user['permission_overrides']??''),true)?:[];if(($json[$permission]??null)==='deny')return false;if(($json[$permission]??null)==='allow')return true;foreach(self::PRESETS[$user['account_type']]??[] as $granted)if($granted==='*'||$granted===$permission||(str_ends_with($granted,'.*')&&str_starts_with($permission,substr($granted,0,-1))))return true;return false;
    }
}
