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
* @ONM\Node(label="User")
*/
class User extends BaseObject
{
    /**
     * @ONM\Id
     */
    private $id;

    /**
     * @ONM\Property(type="string")
     */
    private $username;

    /**
     * @var string
     * @ONM\Property(type="string")
     */
    private $password;

    /**
     * @var array
     */
    private $groups;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

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

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $username = (string) $username;
        if ($this->username !== $username) {
            $this->onPropertyChanged('username', $this->username, $username);
            $this->username = $username;
        }
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $password = (string) $password;
        if ($this->password !== $password) {
            $this->onPropertyChanged('password', $this->password, $password);
            $this->password = $password;
        }
    }

    public function addGroup(Group $group)
    {
        $this->groups->add($group);
        $this->onPropertyChanged('groups', $this->groups, $this->groups);
    }

    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @see PersistableInterface
     *
     * @return array
     */
    public function preparePersistChangeSet()
    {
        $changeSet = array(
            'username' => $this->username,
            'password' => $this->password,
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
