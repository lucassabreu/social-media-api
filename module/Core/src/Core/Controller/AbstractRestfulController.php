<?php

namespace Core\Controller;

use Core\Controller\Exception\AuthenticationException;
use Core\Model\Entity\Entity;
use Exception;
use Zend\Mvc\Controller\AbstractRestfulController as ZendRestfulController;
use Zend\Mvc\MvcEvent;
use Zend\Paginator\Adapter\AdapterInterface;
use Zend\Stdlib\ParametersInterface;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

/**
 * AbstractRestfulController with some alterations to fulfill needs
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com> 
 */
class AbstractRestfulController extends ZendRestfulController {

    /**
     * Handle the request
     *
     * @todo   try-catch in "patch" for patchList should be removed in the future
     * @param  MvcEvent $e
     * @return mixed
     * @throws Exception\DomainException if no route matches in event or invalid HTTP method
     */
    public function onDispatch(MvcEvent $e)
    {
        try {
            return parent::onDispatch($e);
        } catch (AuthenticationException $ae) {
            $model = $this->returnAuthenticationFail();
            $e->setResult($model);
            return $model;
        } catch(Exception $ex) {
            $model = $this->returnError(403, $ex->getMessage());
            $e->setResult($model);
            return $model;
        }
    }

    /**
     * Retrieves the Query parameters from the Request
     * @return ParametersInterface
     */
    protected function getQuery($name, $default = null) {
        return $this->getRequest()->getQuery($name, $default);
    }

    /**
     * Retrieves the Post parameters from the Request
     * @return ParametersInterface
     */
    protected function getPost($name, $default = null) {
        return $this->getRequest()->getPost($name, $default);
    }

    /**
     * Reformat a message and status code to be returned
     * @param $statusCode
     * @param $message
     * @return JsonModel
     */
    protected function returnError($statusCode, $message) {
        $this->setStatusCode($statusCode);
        $model = new JsonModel([
            'error' => [
                'message' => $message,
            ]
        ]);
        $model->setTerminal(true);
        return $model;
    }

    protected function returnAuthenticationFail () {
        return $this->returnError(401, "You must be authenticated to perform this action !");
    }

    /**
     * Sets the Status Code into the Request
     * @param $statusCode
     * @return void
     */
    public function setStatusCode($statusCode) {
        $this->getResponse()->setStatusCode($statusCode);
    }

    /**
     * Execute a Paginator Adapter and retrive a Json array formatted
     * @param $adapter AdapterInterface to be consumed
     * @param $limit Number of items to read
     * @param $offset Where to start 
     * @return array
     */
    protected function convertPaginatorToJson (AdapterInterface $adapter, $limit, $offset) {
        $return = [
            'result' => [],
        ];

        $items = $adapter->getItems($offset, $limit);

        foreach($items as $item)
            $return['result'][] = $this->entityToJson($item);

        $return['paging'] = [
            'count' => count($items),
            'total' => $adapter->count(),
            'offset' => $offset,
        ];

        return $return;
    }

    protected function entityToJson (Entity $ent) {
        return $ent->getData();
    }
}