<?php

namespace Craft;

use Mockery as m;
use PHPUnit_Framework_TestCase;

class SproutFormsServiceTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->formRecord = m::mock('Craft\SproutForms_FormRecord');
        $this->service = new SproutFormsService($this->formRecord);
    }

    public function testGetAllForms()
    {
        $fakeResults = array(array('id' => 1), array('id' => 2));

        $this->formRecord
            ->shouldReceive('findAll')
            ->andReturn($fakeResults);

        $results = $this->service->getAllForms();

        $this->assertEquals(2, count($results));
        $this->assertInstanceOf('Craft\SproutForms_FormModel', $results[0]);
    }
    
    public function testGetFormById()
    {    
        $this->formRecord
        ->shouldReceive('findById')
        ->with(1)
        ->andReturn(true);
        
        $results = $this->service->getFormById(1);
    
        $this->assertInstanceOf('Craft\SproutForms_FormModel', $results);
    }
}
