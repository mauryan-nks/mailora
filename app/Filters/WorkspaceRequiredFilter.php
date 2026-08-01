<?php
namespace App\Filters;
use CodeIgniter\Filters\FilterInterface;use CodeIgniter\HTTP\RequestInterface;use CodeIgniter\HTTP\ResponseInterface;
class WorkspaceRequiredFilter implements FilterInterface { public function before(RequestInterface$request,$arguments=null){if(!service('tenantContext')->workspaceId())return redirect()->to('/clients')->with('error','Open a customer workspace before using this module.');return null;}public function after(RequestInterface$request,ResponseInterface$response,$arguments=null){} }
