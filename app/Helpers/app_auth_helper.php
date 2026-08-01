<?php
if(!function_exists('app_auth')){function app_auth(): \App\Libraries\AuthService{return service('appAuth');}}
if(!function_exists('current_user')){function current_user(): ?array{return app_auth()->user();}}
if(!function_exists('portal_home')){function portal_home():string{$u=current_user();if(!$u)return'/login';if(session('active_workspace_id')||$u['account_type']==='customer')return'/app/dashboard';return in_array($u['account_type'],['platform_admin','platform_team'],true)?'/admin/dashboard':'/reseller/dashboard';}}
if(!function_exists('portal_url')){function portal_url(string$path=''):string{$u=current_user();$root=in_array($u['account_type'],['platform_admin','platform_team'],true)?'admin':(in_array($u['account_type'],['reseller','reseller_team'],true)?'reseller':'app');return base_url($root.'/'.ltrim($path,'/'));}}
if(!function_exists('workspace_url')){function workspace_url(string$path=''):string{return base_url('app/'.ltrim($path,'/'));}}
