<?php

namespace Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper\Collections\LazyCollection;
use Doctrine\SkeletonMapper\Hydrator\HydratableInterface;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\Persister\PersistableInterface;
use Doctrine\SkeletonMapper\UnitOfWork\Change;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;

use Fervo\ONM\Mapping\Annotations as ONM;

/**
* @ONM\Node(label="Group")
*/
class Group extends BaseObject
{
    /**
     * @ONM\Id
     */
    private $id;

    /**
     * @ONM\Property(type="string")
     */
    private $name;

    public function getId()
    {
        return (int) $this->id;
    }

    public function setId($id)
    {
        $id = (int) $id;
        if ($this->id !== $id) {
            $this->onPropertyChanged('id', $this->id, $id);
            $this->id = $id;
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $name = (string) $name;
        if ($this->name !== $name) {
            $this->onPropertyChanged('name', $this->name, $name);
            $this->name = $name;
        }
    }

    /**
     * @see PersistableInterface
     *
     * @return array
     */
    public function preparePersistChangeSet()
    {
        $changeSet = array(
            'name' => $this->name,
        );
        if ($this->id !== null) {
            $changeSet['id'] = (int) $this->id;
        }
        return $changeSet;
    }

    /**
     * @see PersistableInterface
     *
     * @param \Doctrine\SkeletonMapper\UnitOfWork\ChangeSet $changeSet
     *
     * @return array
     */
    public function prepareUpdateChangeSet(ChangeSet $changeSet)
    {
        $changeSet = array_map(function (Change $change) {
            return $change->getNewValue();
        }, $changeSet->getChanges());
        $changeSet['id'] = (int) $this->id;
        return $changeSet;
    }
}
