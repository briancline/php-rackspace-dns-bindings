<?php

namespace Prado\Rackspace\DNS\EntityManager;

use DateTime;
use Prado\Rackspace\DNS\Http\Client;
use Prado\Rackspace\DNS\Entity\AsynchResponse;
use Prado\Rackspace\DNS\Entity\Domain;
use Prado\Rackspace\DNS\Entity\DomainList;
use Prado\Rackspace\DNS\Entity\Record;
use Prado\Rackspace\DNS\Hydrator;
use Prado\Rackspace\DNS\UriGenerator;
use Prado\Rackspace\DNS\Model\Entity;
use Prado\Rackspace\DNS\Model\EntityManager;

class DomainManager implements EntityManager
{
    /**
     * @var Prado\Rackspace\DNS\Http\Client
     */
    protected $_client;
    
    /**
     * @var Prado\Rackspace\DNS\Hydrator
     */
    protected $_hydrator;
    
    /**
     * Constructor.
     * 
     * @param Prado\Rackspace\Http\Client  $client
     * @param Prado\Rackspace\DNS\Hydrator $hydrator
     */
    public function __construct(Client $client, Hydrator $hydrator)
    {
        $this->_client   = $client;
        $this->_hydrator = $hydrator;
    }
    
    public function create(Entity $entity)
    {
        $object = array();
        foreach (array('name', 'ttl', 'emailAddress', 'comment') as $field) {
            $get = sprintf('get%s', ucfirst($field));
            if ($entity->$get()) {
                $object[$field] = $entity->$get();
            }
        }
        
        $uri = $this->_uriGenerator->getUri('/domains');
        $response = $this->_client->post($uri, array(
        	'domains' => array($object)
        ));
        
        $json = json_decode($response->getBody(), TRUE);
        $asynchResponse = new AsynchResponse;
        $this->_hydrator->hydrateEntity($asynchResponse, $json);
        
        return $asynchResponse;
    }
    
    public function remove(Entity $entity)
    {
        if (!$domain->getId()) {
            throw new \BadMethodCallException('Must set the ID of the domain you want to remove.');
        }
        
        $this->_client->resetParameters();
        $uri = sprintf('https://dns.api.rackspacecloud.com/v1.0/%s/domains/%s', $this->accountId, $domain->getId());
        $this->_client->setUri($uri);
        
        $response = $this->_client->request(ZendClient::DELETE);
        
        if ($response->isError()) {
            throw new HttpException($response->getStatus(), $response->getBody());
        }
        
        return TRUE;
    }
    
    public function update(Entity $entity)
    {
    }
    
    public function find($id)
    {
        $response = $this->_client->get(sprintf('/domains/%s', $id));
        
        $json = json_decode($response->getBody(), TRUE);
        
        $entity = new Domain();
        $this->_hydrator->hydrateEntity($entity, $json);
        
        foreach ($json['recordsList']['records'] as $jsonRecord) {
            
            $record = new Record();
            $this->_hydrator->hydrateEntity($record, $jsonRecord);
            $entity->addRecord($record);
        }
        
        return $entity;
    }
    
    public function createList()
    {
        $response = $this->_client->get('/domains');
        
        $json = json_decode($response->getBody(), TRUE);
        
        $list = new DomainList();
        foreach ($json['domains'] as $jsonDomain) {
            
            $entity = new Domain();
            $this->_hydrator->hydrateEntity($entity, $jsonDomain);
            $list->addEntity($entity);
        }
        
        return $list;
    }
}