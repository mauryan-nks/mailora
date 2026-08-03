<?php
namespace App\Models;
use CodeIgniter\Model;
class CampaignModel extends Model
{
    protected $table='campaigns'; protected $returnType='array'; protected $useTimestamps=true;
    protected $allowedFields=['workspace_id','name','subject','preview_text','from_name','from_email','content_html','content_text','editor_type','template_id','smtp_id','status','segment_id','scheduled_at','timezone','sent_at'];
    protected $validationRules=['name'=>'required|max_length[160]','subject'=>'required|max_length[190]','from_email'=>'required|valid_email','status'=>'in_list[draft,scheduled,sending,sent,paused]'];
}
