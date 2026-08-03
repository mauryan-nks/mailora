<?php

namespace App\Models;

use CodeIgniter\Model;

class FormModel extends Model
{
    protected $table = 'forms';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'workspace_id',
        'name',
        'form_type',
        'slug',
        'headline',
        'fields',
        'status',
        'design_style',
        'background_color',
        'accent_color',
        'parallax',
        'submissions',
        'created_at',
        'updated_at',
    ];
}
