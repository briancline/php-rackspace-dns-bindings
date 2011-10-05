<?php

namespace Prado\Rackspace\Storage;

use Symfony\Component\HttpFoundation\Session;

class SymfonySessionStorageAdapter implements StorageInterface
{
    protected $_session;
    
    public function __construct(Session $session)
    {
        $this->_session = $session;
    }
    
    public function retrieve($key)
    {
        $this->_session->get($key, NULL);
    }
    
    public function store($key, $value)
    {
        $this->_session->set($key, $value);
    }
}
