<?php

use PHPUnit\Framework\TestCase;
use IU\PHPCap\RedCapApiConnection;
use IU\PHPCap\RedCapProject;

/**
 * PHPUnit tests for the RedCapProject class.
 */
class RedCapProjectTest extends TestCase {
    private static $config;
    private static $basicDemographyProject;
    
    public static function setUpBeforeClass()
    {
        self::$config = parse_ini_file('config.ini');
        self::$basicDemographyProject = new RedCapProject(self::$config['api.url'], self::$config['basic.demography.api.token']);
    }
    
    public function testExportProjectInfo()
    {
        $callInfo = true;
        $result = self::$basicDemographyProject->exportProjectInfo($callInfo);
        
        $this->assertEquals($result['project_language'], 'English', 'Project info "project_language" test.');
        $this->assertEquals($result['purpose_other'], 'PHPCap testing', 'Project info "purpose_other" test.');
    }
    
    public function testExportMetadata()
    {
        $result = self::$basicDemographyProject->exportMetadata();
         
        $this->assertArrayHasKey('field_name', $result[0], 'Metadata has field_name field test.');
        $this->assertEquals($result[0]['field_name'], 'record_id', 'Metadata has study_id field test.');
    
        $callInfo = self::$basicDemographyProject->getCallInfo();
     
        $this->assertEquals($callInfo['url'], self::$config['api.url'], 'Metadata url test.');
        $this->assertArrayHasKey('content_type', $callInfo, 'Metadata has content type test.');
        $this->assertArrayHasKey('http_code', $callInfo, 'Metadata has HTTP code test.');
    }
    
    public function testExportRecords()
    {
        $result = self::$basicDemographyProject->exportRecords();
        
        $this->assertEquals(count($result), 100, 'Number of records test.');
        
        $recordIds = array_column($result, 'record_id');
        $this->assertEquals(min($recordIds), 1001, 'Min record_id test.');
        $this->assertEquals(max($recordIds), 1100, 'Max record_id test.');
        
        $lastNameMap = array_flip(array_column($result, 'last_name'));
        $this->assertArrayHasKey('Braun',  $lastNameMap, 'Has last name test.');
        $this->assertArrayHasKey('Carter', $lastNameMap, 'Has last name test.');
        $this->assertArrayHasKey('Hayes',  $lastNameMap, 'Has last name test.');
    }
    
    public function testExportRecordsWithFilterLogic()
    {
        $result = self::$basicDemographyProject->exportRecords('php', 'flat', null, null, null, null, "[last_name] = 'Thiel'");
        $this->assertEquals(count($result), 2);
        $firstNameMap = array_flip(array_column($result, 'first_name'));
        $this->assertArrayHasKey('Suzanne', $firstNameMap, 'Has first name test.');
        $this->assertArrayHasKey('Kaia', $firstNameMap, 'Has first name test.');        
    }
    
    
    public function testExportRedcapVersion()
    {
        $result = self::$basicDemographyProject->exportRedcapVersion();
        $this->assertRegExp('/^[0-9]+\.[0-9]+\.[0-9]+$/', $result, 'REDCap version format test.');
    }

}
