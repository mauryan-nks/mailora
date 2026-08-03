<?php

namespace App\Models;

use CodeIgniter\Model;

class SmtpAccountModel extends Model
{
    protected $table = 'smtp_accounts';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'workspace_id',
        'provider',
        'host',
        'port',
        'username',
        'encrypted_password',
        'encryption',
        'from_email',
        'from_name',
        'is_active',
        'created_at',
        'updated_at',
    ];
}
