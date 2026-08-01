<?php
use App\Libraries\ContactService;use App\Libraries\PermissionService;use App\Libraries\PlanQuotaService;use App\Support\Uuid;use CodeIgniter\Test\CIUnitTestCase;
final class FoundationTest extends CIUnitTestCase
{
    public function testUuidV4IsValidAndUnique():void{$a=Uuid::v4();$b=Uuid::v4();$this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',$a);$this->assertNotSame($a,$b);}
    public function testPlatformAdminPresetAllowsEveryPermission():void{$user=['id'=>1,'account_type'=>'platform_admin','permission_overrides'=>null];$this->assertTrue((new PermissionService())->allows($user,'smtp.delete'));}
    public function testContactEmailNormalization():void{$this->assertSame('person@example.com',(new ContactService())->normalizeEmail('  Person@Example.COM '));}
    public function testFiniteAndUnlimitedPlanLimits():void{$quota=new PlanQuotaService();$this->assertTrue($quota->limitReached(5,5));$this->assertFalse($quota->limitReached(5,4));$this->assertFalse($quota->limitReached(null,1000000));}
}
