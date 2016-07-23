<?php

namespace ApplicationTest\Controller;

use Zend\StdLib\ArrayUtils;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class IndexControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $config = __DIR__ . '/../../../../config/test/application.config.php';
        
        if (file_exists($config))
        {
            $this->setApplicationConfig(
                ArrayUtils::merge(
                    include __DIR__ . '/../../../../config/application.config.php',
                    include $config
                )
            );
        }
        else {
            $this->setApplicationConfig(
                include __DIR__ . '/../../../../config/application.config.php'
            );
        } 
        parent::setUp();
    }

    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('application');
        $this->assertControllerName('Application\Controller\Index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('home');
    }
}