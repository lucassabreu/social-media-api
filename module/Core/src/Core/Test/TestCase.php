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
abstract class TestCase extends AbstractHttpControllerTestCase {

    public function setup() {
        parent::setup();
        $this->createDatabase();
    }

    public function tearDown() {
        parent::tearDown();
        $this->dropDatabase();
    }

    /**
     * @return void
     */
    public function createDatabase() {
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
        if (isset($queries['ddl'])) {
            $queries = $config['ddl'];
            foreach ($queries as $queries) {
                foreach ($queries['create'] as $query)
                    $dbAdapter->query($query, Adapter::QUERY_MODE_EXECUTE);
            }
        }
    }

    /**
     * @return void
     */
    public function dropDatabase() {
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
        if (isset($queries['ddl'])) {
            $queries = $config['ddl'];
            foreach ($queries as $query) {
                $dbAdapter->query($query['drop'], Adapter::QUERY_MODE_EXECUTE);
            }
        }
    }

    /**
     * 
     * @return Adapter
     */
    public function getAdapter() {
        return $this->getServiceManager()->get('DbAdapter');
    }

    /**
     * Retrieve the current ServiceManager.
     * @return ServiceManager
     */
    public function getServiceManager() {
        return $this->getApplication()->getServiceManager();
    }

    /**
     * Retrieve Service
     *
     * @param  string $service
     * @return ServiceLocatorAwareInterface
     */
    protected function getService($service) {
        return $this->getServiceManager()->get($service);
    }

}