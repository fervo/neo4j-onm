<?php

namespace Fervo\ONM\Hydrator;

use Doctrine\SkeletonMapper\Hydrator\ObjectHydrator;
use Doctrine\SkeletonMapper\ObjectManager;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\LazyLoadingInterface;

/**
*
*/
class Neo4jObjectHydrator extends ObjectHydrator
{
    protected $om;
    protected $proxyFactory;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->proxyFactory = new LazyLoadingValueHolderFactory();
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

        foreach ($cmd->getAssociationNames() as $association) {
            if ($cmd->isSingleValuedAssociation($association)) {
                $targetClass = $cmd->getAssociationTargetClass($association);
                $instance = $this->proxyFactory->createProxy($targetClass, function (&$wrappedObject, LazyLoadingInterface $proxy, $method, array $parameters, &$initializer) use ($object, $targetClass) {
                    $initializer   = null; // disable initialization
                    $wrappedObject = $this->om->getRepository($targetClass)->findOneRelated($object, )

                    return true; // confirm that initialization occurred correctly
                });




            }
        }







        return $object;
    }
}
