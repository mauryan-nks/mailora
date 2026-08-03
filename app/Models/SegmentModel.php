<?php

namespace App\Models;

use CodeIgniter\Model;

class SegmentModel extends Model
{
    protected $table = 'segments';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'workspace_id',
        'name',
        'rules',
        'created_at',
        'updated_at',
    ];
}
