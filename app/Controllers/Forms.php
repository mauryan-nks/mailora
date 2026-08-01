<?php
namespace App\Controllers;
class Forms extends BaseController { public function index(): string { return $this->page('simple/cards',['title'=>'Forms & landing pages','active'=>'forms','subtitle'=>'Embedded forms, popups and hosted landing pages','items'=>db_connect()->table('forms')->where('workspace_id',$this->workspaceId)->get()->getResultArray()]); } }
