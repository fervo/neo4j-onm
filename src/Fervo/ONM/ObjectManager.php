<?php

namespace Fervo\ONM;

use Doctrine\Common\Persistence\ObjectManager as ObjectManagerInterface;
use Doctrine\Common\EventManager;

/**
* 
*/
class ObjectManager implements ObjectManagerInterface
{
    protected $conn;
    protected $config;
    protected $eventManager;
    protected $metadataFactory;

    /**
     * Creates a new EntityManager that operates on the given database connection
     * and uses the given Configuration and EventManager implementations.
     *
     * @param \Doctrine\DBAL\Connection $conn
     * @param \Fervo\ONM\ConfigurationInterface $config
     * @param \Doctrine\Common\EventManager $eventManager
     */
    public function __construct($conn, ConfigurationInterface $config, EventManager $eventManager)
    {
        $this->conn         = $conn;
        $this->config       = $config;
        $this->eventManager = $eventManager;

        $metadataFactoryClassName = $config->getClassMetadataFactoryName();

        $this->metadataFactory = new $metadataFactoryClassName;
        $this->metadataFactory->setObjectManager($this);
        $this->metadataFactory->setCacheDriver($this->config->getMetadataCacheImpl());

//        $this->unitOfWork   = new UnitOfWork($this);
/*        $this->proxyFactory = new ProxyFactory(
            $this,
            $config->getProxyDir(),
            $config->getProxyNamespace(),
            $config->getAutoGenerateProxyClasses()
        );*/
    }

    public function find($className, $id)
    {
        return null;
    }

    public function persist($object)
    {

    }

    public function remove($object)
    {

    }

    public function merge($object)
    {
        return null;
    }

    public function clear($objectName = null)
    {

    }

    public function detach($object)
    {

    }

    public function refresh($object)
    {

    }

    public function flush()
    {

    }

    public function getRepository($className)
    {
        return null;
    }

    public function getClassMetadata($className)
    {
        return $this->metadataFactory->getMetadataFor($className);
    }

    public function getMetadataFactory()
    {
        return $this->metadataFactory;
    }

    public function initializeObject($obj)
    {

    }

    public function contains($object)
    {
        return false;
    }

    public function getConfiguration()
    {
        return $this->config;
    }

    public function getEventManager()
    {
        return $this->eventManager;
    }
}
