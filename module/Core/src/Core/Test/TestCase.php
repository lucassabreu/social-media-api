<?php

namespace Core\Test;

use Core\Test\Bootstrap;
use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Base Test Case class
 */
abstract class TestCase extends AbstractHttpControllerTestCase
{
    protected $setUpDatabase = false;
    
    public function setUp()
    {
        parent::setup();
        if ($this->setUpDatabase == true) {
            $this->createDatabase();
        }
    }

    public function tearDown()
    {
        parent::tearDown();
        if ($this->setUpDatabase) {
            $this->dropDatabase();
        }
    }

    /**
     * @return void
     */
    public function createDatabase()
    {
        if ($this->getServiceManager()->has('Doctrine\ORM\EntityManager')) {
            $this->generateSchema();
        } else {
            $dbAdapter = $this->getAdapter();

            if (get_class($dbAdapter->getPlatform()) == 'Zend\Db\Adapter\Platform\Sqlite') {
                //enable foreign keys on sqlite
                $dbAdapter->query('PRAGMA foreign_keys = ON;', Adapter::QUERY_MODE_EXECUTE);
            }

            if (get_class($dbAdapter->getPlatform()) == 'Zend\Db\Adapter\Platform\Mysql') {
                //enable foreign keys on mysql
                $dbAdapter->query('SET FOREIGN_KEY_CHECKS = 1;', Adapter::QUERY_MODE_EXECUTE);
            }

            $config = \Bootstrap::getTestConfig();
            if (isset($config['ddl'])) {
                $queries = $config['ddl'];
                foreach ($queries as $queries) {
                    foreach ($queries['create'] as $query) {
                        $dbAdapter->query($query, Adapter::QUERY_MODE_EXECUTE);
                    }
                }
            }
        }
    }

    /**
     * @return null
     */
    protected function generateSchema()
    {
        $em = $this->getServiceManager()->get('Doctrine\ORM\EntityManager');
        $metadatas = $em->getMetaDataFactory()->getAllMetaData();
        if (!empty($metadatas)) {
            $tool = new \Doctrine\ORM\Tools\SchemaTool($em);
            $tool->dropSchema($metadatas);
            $tool->createSchema($metadatas);
        }
    }

    /**
     * @return void
     */
    public function dropDatabase()
    {
        if (!$this->getServiceManager()->has('Doctrine\ORM\EntityManager')) {
            $dbAdapter = $this->getAdapter();

            if (get_class($dbAdapter->getPlatform()) == 'Zend\Db\Adapter\Platform\Sqlite') {
                //disable foreign keys on sqlite
                $dbAdapter->query('PRAGMA foreign_keys = OFF;', Adapter::QUERY_MODE_EXECUTE);
            }
            if (get_class($dbAdapter->getPlatform()) == 'Zend\Db\Adapter\Platform\Mysql') {
                //disable foreign keys on mysql
                $dbAdapter->query('SET FOREIGN_KEY_CHECKS = 0;', Adapter::QUERY_MODE_EXECUTE);
            }

            $config = \Bootstrap::getTestConfig();
            if (isset($config['ddl'])) {
                $queries = $config['ddl'];
                foreach ($queries as $query) {
                    $dbAdapter->query($query['drop'], Adapter::QUERY_MODE_EXECUTE);
                }
            }
        }
    }

    /**
     *
     * @return Adapter
     */
    public function getAdapter()
    {
        return $this->getServiceManager()->get('DbAdapter');
    }

    /**
     * Retrieve the current ServiceManager.
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->getApplication()->getServiceManager();
    }

    /**
     * Retrieve Service
     *
     * @param  string $service
     * @return ServiceLocatorAwareInterface
     */
    protected function getService($service)
    {
        return $this->getServiceManager()->get($service);
    }

    /**
     * Retrieves the ViewModel returned by the Controller
     * return \Zend\View\Model\ViewModel
     */
    protected function getViewModel()
    {
        return $this->getApplication()->getMvcEvent()->getViewModel();
    }
}
