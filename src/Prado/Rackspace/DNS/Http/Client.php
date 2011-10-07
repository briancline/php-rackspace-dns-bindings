<?php

namespace Prado\Rackspace\DNS\Http;

use Zend\Http\Client as ZendClient;
use Zend\Http\Request as ZendRequest;

class Client
{
    /**
     * @var Zend\Http\Client
     */
    protected $_client;
    
    /**
     * @var Prado\Rackspace\DNS\Http\RequestGenerator
     */
    protected $_generator;
    
    /**
     * Constructor.
     *  
     * @param Zend\Http\Client $client
     */
    public function __construct(ZendClient $client, RequestGenerator $generator)
    {
        $this->_client    = $client;
        $this->_generator = $generator;
    }
    
    /**
     * @param string $uri The path to the resource
     * @return Zend\Http\Response The Zend Response generated
     */
    public function get($path)
    {
        $request = $this->_generator->createRequest($path);
        $request->setMethod(ZendRequest::METHOD_GET);
        
        $response = $this->_client->send($request);
    
        if (!$response->isSuccess()) {
            throw new \Exception($response->getBody(), $response->getStatusCode());
        }
        
        return $response;
    }
    
    /**
     * @param string $uri The path to the resource
     * @return Zend\Http\Response The Zend Response generated.
     */
    public function post($path, $data)
    {
        $request = $this->_generator->createRequest($path);
        $request->setMethod(ZendRequest::METHOD_POST);
        
        $content = json_encode($data);
        $request->setContent($content);
        $request->headers()->addHeaders(array('Content-Length' => strlen($content)));
        
        $response = $this->_client->send($request);
        
        if (!$response->isSuccess()) {
            throw new \Exception($response->getBody(), $response->getStatusCode());
        }
        
        return $response;
    }
}
