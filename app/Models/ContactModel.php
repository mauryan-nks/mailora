<?php
namespace App\Models;
use CodeIgniter\Model;
class ContactModel extends Model
{
    protected $table='contacts'; protected $returnType='array'; protected $useTimestamps=true; protected $useSoftDeletes=true;
    protected $allowedFields=['uuid','workspace_id','reseller_id','email','normalized_email','first_name','last_name','phone','birthday','company','job_title','website','address_line_1','address_line_2','city','state','postal_code','country','latitude','longitude','google_place_id','source','source_reference','status','custom_fields','notes','consent_status','consent_source','consent_ip','consented_at','last_opened_at','last_clicked_at','last_emailed_at','score','owner_user_id','created_by'];
    protected $validationRules=['workspace_id'=>'required|integer','email'=>'required|valid_email|max_length[190]','status'=>'permit_empty|in_list[subscribed,unsubscribed,bounced,complained]'];
}
