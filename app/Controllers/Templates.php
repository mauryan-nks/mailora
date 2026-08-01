<?php
namespace App\Controllers;
class Templates extends BaseController { public function index(): string { return $this->page('simple/cards',['title'=>'Templates','active'=>'templates','subtitle'=>'Ready-to-customize campaign layouts','items'=>db_connect()->table('templates')->where('workspace_id',$this->workspaceId)->get()->getResultArray()]); } }
