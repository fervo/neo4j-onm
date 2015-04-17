<?php

namespace Fervo\ONM\Hydrator;

use Doctrine\SkeletonMapper\Hydrator\ObjectHydrator;
use Doctrine\SkeletonMapper\ObjectManager;

/**
*
*/
class Neo4jObjectHydrator extends ObjectHydrator
{
    protected $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * @param \Doctrine\SkeletonMapper\Hydrator\HydratableInterface $object
     * @param array                                                 $data
     */
    public function hydrate($object, array $data)
    {
        $cmd = $this->om
            ->getClassMetadata(get_class($object));

        foreach ($cmd->getFieldNames() as $field) {
            if (isset($data[$field])) {
                $cmd->setFieldValue($object, $field, $data[$field]);
            }
        }

        return $object;
    }
}
