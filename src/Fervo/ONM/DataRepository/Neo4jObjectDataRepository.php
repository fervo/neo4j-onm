<?php

namespace Fervo\ONM\DataRepository;

use Neo4j\Neo4jPDO;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\DataRepository\BasicObjectDataRepository;

/**
 * Base class for DBAL object data repositories to extend from.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class Neo4jObjectDataRepository extends BasicObjectDataRepository
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

    public function findAll()
    {
        $label = $this->getLabel();
        $q = $this->getConnection()->prepare('MATCH (n:'.$label.') RETURN n');
        $q->execute();

        return $q->fetchAll();
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $label = $this->getLabel();

        $params = [];
        foreach ($criteria as $key => $value) {
            $params[] = $key.': {criteria}.'.$key;
        }

        $q = $this->getConnection()->prepare('MATCH (n:'.$label.' {'.implode(', ', $params).'}) RETURN n');
        $q->bindParam('criteria', $criteria);
        $q->execute();

        $out = [];
        foreach ($q->fetchAll() as $row) {
            $out[] = $row['n'];
        }

        return $out;
    }

    public function findOneBy(array $criteria)
    {
        return current($this->findBy($criteria)) ?: null;
    }
}
