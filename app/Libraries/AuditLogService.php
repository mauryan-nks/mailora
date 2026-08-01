<?php
namespace App\Libraries;
use App\Support\Uuid;
class AuditLogService
{
    public function record(string $event, ?array $actor=null, array $metadata=[], ?string $subjectType=null, ?string $subjectUuid=null): void
    {
        unset($metadata['password'],$metadata['password_hash'],$metadata['secret'],$metadata['token']);
        $request=service('request');db_connect()->table('audit_logs')->insert(['uuid'=>Uuid::v4(),'actor_user_id'=>$actor['id']??null,'reseller_id'=>$actor['reseller_id']??null,'workspace_id'=>$actor['workspace_id']??null,'event'=>$event,'subject_type'=>$subjectType,'subject_uuid'=>$subjectUuid,'metadata'=>$metadata?json_encode($metadata,JSON_THROW_ON_ERROR):null,'ip_address'=>$request->getIPAddress(),'user_agent'=>substr((string)$request->getUserAgent(),0,500),'created_at'=>date('Y-m-d H:i:s')]);
    }
}
