<?php

require '../includes.php';

class TestOfPublishingControl extends UnitTestCase
{
    // Constructor

    public function setUp()
    {
        echo "Testing: PublishingControl Library\r\n";
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Tests the PublishingGroupControl::createGroup method
     */
    public function testPublishingGroupControl_createGroup()
    {
        // Declare Variables
        $p = new PublishingGroupControl();
        $gobj = new GroupObject();
        $passed = false;

        echo "\r\n---------------------------------------------------\r\nTEST -- PublishingGroupControl::createGroup\r\n---------------------------------------------------\r\n";

        try
        {
            $p->createGroup($gobj);
        }
        catch(PublishingException $e)
        {
            // It must be caught
            $passed = true;
        }

        echo "Testing invalid group input...\r\n";
        $this->assertEqual($passed, TRUE);
        $passed = false;
        
        try
        {
            echo "Testing valid group input..\r\n";
            $gobj->_Title = "Random";
            $gobj->_Summary = "Random Words";
            $gobj->_Type = TypeObject::O_PUBLIC;
            $p->createGroup($gobj);
        }
        catch(PublishingException $e)
        {
            $this->fail($e->getMessage());
        }

        

    }

   

}



?>