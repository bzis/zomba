<?php
namespace Vifeed\SystemBundle\Tests;

class SmsTest extends ApiTestCase {


    /**
     *
     */
    public function testSms()
    {
        $manager = $this->getContainer()->get('vifeed.sms_manager');
        //$manager->send('blabla', '+79055141362');
    }
}
 