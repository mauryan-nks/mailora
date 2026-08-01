<?php
namespace App\Commands;
use App\Libraries\ContactImportService;use CodeIgniter\CLI\BaseCommand;use CodeIgniter\CLI\CLI;
class ProcessContactImports extends BaseCommand { protected $group='Mailora';protected $name='imports:process';protected $description='Process queued contact imports.';public function run(array$params):void{$limit=max(1,(int)($params[0]??10));$service=new ContactImportService();$done=0;while($done<$limit&&$service->processNext())$done++;CLI::write("Processed {$done} import job(s).",'green');} }
