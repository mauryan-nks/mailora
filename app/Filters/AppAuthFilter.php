<?php
namespace App\Filters;
use CodeIgniter\Filters\FilterInterface;use CodeIgniter\HTTP\RequestInterface;use CodeIgniter\HTTP\ResponseInterface;
class AppAuthFilter implements FilterInterface { public function before(RequestInterface $request,$arguments=null){if(!app_auth()->loggedIn())return redirect()->to('/login')->with('error','Please sign in to continue.');return null;}public function after(RequestInterface $request,ResponseInterface $response,$arguments=null){} }
