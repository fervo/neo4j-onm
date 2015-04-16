<?php

namespace Fervo\ONM\Persister;

use Neo4j\Neo4jPDO;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;
use Doctrine\SkeletonMapper\Persister\BasicObjectPersister;

class Neo4jObjectPersister extends BasicObjectPersister
{
    /**
     * @var \Neo4j\Neo4jPDO
     */
    protected $connection;

    /**
     * @var string
     */
    protected $label;

    /**
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface $objectManager
     * @param \Doctrine\DBAL\Connection                       $connection
     * @param string                                          $className
     * @param string                                          $label
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Neo4jPDO $connection,
        $className = null,
        $label = null)
    {
        parent::__construct($objectManager, $className);
        $this->connection = $connection;
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return \Neo4j\Neo4jPDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    public function persistObject($object)
    {
        $data = $this->preparePersistChangeSet($object);
        $label = $this->getLabel();

        $q = $this->getConnection()->prepare('CREATE (n:'.$label.' {data}) RETURN n');
        $q->bindParam('data', $data);
        $q->execute();

        $newData = $q->fetchAll()[0][0];

        return $newData;
    }

    public function updateObject($object, ChangeSet $changeSet)
    {
        $data = $this->prepareUpdateChangeSet($object, $changeSet);
        $label = $this->getLabel();

        $identifier = $this->getObjectIdentifier($object);

        $criteria = [];
        foreach ($identifier as $key => $value) {
            $criteria[] = $key.': '.$value;
        }

        $params = [];
        foreach ($data as $key => $value) {
            $params[] = 'n.'.$key.' = {data}.'.$key;
        }

        $q = $this->getConnection()->prepare(
            'MERGE (n:'.$label.' {'.implode(', ', $criteria).'})
            ON MATCH SET '.implode(', ', $params).'
            RETURN n'
        );
        $q->bindParam('data', $data);
        $q->execute();

        $newData = $q->fetchAll()[0][0];

        return $newData;
    }

    public function removeObject($object)
    {
        $label = $this->getLabel();
        $identifier = $this->getObjectIdentifier($object);

        $criteria = [];
        foreach ($identifier as $key => $value) {
            $criteria[] = $key.': '.$value;
        }

        $q = $this->getConnection()->prepare(
            'MERGE (n:'.$label.' {'.implode(', ', $criteria).'})
            DELETE n'
        );
        $q->execute();
    }

    /**
     * @param object $object
     *
     * @return array $identifier
     */
    protected function getObjectIdentifier($object)
    {
        return $this->objectManager
            ->getRepository(get_class($object))
            ->getObjectIdentifier($object);
    }
}
