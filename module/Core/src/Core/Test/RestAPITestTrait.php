<?php

namespace Core\Test;

use Zend\Http\Request;

/**
 * Trait to be used along with <code>AbstractHttpControllerTestCase</code> and provide some help on 
 * calling for controllers by <code>Resquest</code>
 * 
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 * @see \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
 */
trait RestAPITestTrait {

    /**
     * Create a new Request based on paramenters
     * @param $uri URI to be called
     * @param $method optional HTTP method to be used, default is GET
     * @param $query optional Query parameters, default is "empty"
     * @param $post optional Post parameters, default is "empty"
     * @return Request
     */
    private function newRequest($uri, $method = "GET", $query = array(), $post = array()) {
        $request = new Request();
        $request->setUri($uri);
        $request->setMethod($method);

        foreach($query as $param => $value)
            $request->getQuery()->add($param, $value);

        foreach($post as $param => $value)
            $request->getPost()->add($param, $value);

        return $request;
    }

    /**
     * Create a new Request based on paramenters and dispath it
     *
     * @param $uri URI to be called
     * @param $method optional HTTP method to be used, default is GET
     * @param $query optional Query parameters, default is "empty"
     * @param $post optional Post parameters, default is "empty"
     * @return void
     */
    private function dispatchNewRequest ($uri, $method = "GET", $query = array(), $post = array()) {
        $this->dispatch($this->newRequest($uri, $method, $query, $post));
    }

}