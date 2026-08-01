<?php
namespace App\Libraries;
final class TenantContext { private ?array $user=null;private ?int $workspaceId=null;private ?int $resellerId=null;public function set(array $u,?int $w,?int $r):void{$this->user=$u;$this->workspaceId=$w;$this->resellerId=$r;}public function user():?array{return$this->user;}public function workspaceId():?int{return$this->workspaceId;}public function resellerId():?int{return$this->resellerId;} }
