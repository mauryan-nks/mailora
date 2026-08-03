<?php

namespace App\Models;

use CodeIgniter\Model;

class TemplateModel extends Model
{
    protected $table = 'templates';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'workspace_id',
        'name',
        'category',
        'thumbnail',
        'content_html',
        'editor_type',
        'source_url',
        'created_at',
        'updated_at',
    ];
}
