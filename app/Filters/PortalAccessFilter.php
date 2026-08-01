<?php
namespace App\Filters;
use CodeIgniter\Filters\FilterInterface;use CodeIgniter\HTTP\RequestInterface;use CodeIgniter\HTTP\ResponseInterface;
class PortalAccessFilter implements FilterInterface { public function before(RequestInterface$request,$arguments=null){$u=current_user();$area=$arguments[0]??'';$ok=match($area){'admin'=>in_array($u['account_type'],['platform_admin','platform_team'],true),'reseller'=>in_array($u['account_type'],['reseller','reseller_team'],true),'workspace'=>$u['account_type']==='customer'||((int)session('active_workspace_id')>0),default=>false};if(!$ok)return redirect()->to(portal_home())->with('error','You do not have access to that portal.');return null;}public function after(RequestInterface$request,ResponseInterface$response,$arguments=null){} }
