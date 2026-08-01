<?php
namespace App\Controllers;
class Portal extends BaseController { public function home(){if(!app_auth()->loggedIn())return redirect()->to('/login');return redirect()->to(portal_home());} }
