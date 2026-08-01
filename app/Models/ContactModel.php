<?php
namespace App\Models;
use CodeIgniter\Model;
class ContactModel extends Model
{
    protected $table='contacts'; protected $returnType='array'; protected $useTimestamps=true; protected $useSoftDeletes=true;
    protected $allowedFields=['workspace_id','email','first_name','last_name','phone','birthday','status','source','custom_fields'];
    protected $validationRules=['workspace_id'=>'required|integer','email'=>'required|valid_email|max_length[190]','status'=>'permit_empty|in_list[subscribed,unsubscribed,bounced,complained]'];
}
