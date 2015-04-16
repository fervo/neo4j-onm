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
     * @var \Doctrine\SkeletonMapper\Tests\Model\Profile
     */
    private $profile;
    /**
     * @var array
     */
    private $groups;
    /**
     * @var array
     */
    public $called = array();
    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }
    /**
     * Assign identifier to object.
     *
     * @param array $identifier
     */
    public function assignIdentifier(array $identifier)
    {
        $this->id = (int) $identifier['id'];
    }
    public static function loadMetadata(ClassMetadataInterface $metadata)
    {
        $metadata->identifier = array('id');
        $metadata->identifierFieldNames = array('id');
        $metadata->mapField(array(
            'name' => 'id',
            'fieldName' => 'id',
        ));
        $metadata->mapField(array(
            'fieldName' => 'username',
        ));
        $metadata->mapField(array(
            'fieldName' => 'password',
        ));
        $metadata->mapField(array(
            'name' => 'profileId',
            'fieldName' => 'profile',
        ));
        $metadata->mapField(array(
            'name' => 'groupIds',
            'fieldName' => 'groups',
        ));
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
    public function getProfile()
    {
        if ($this->profile instanceof \Closure) {
            $this->profile = $this->profile->__invoke();
        }
        return $this->profile;
    }
    public function setProfile(Profile $profile)
    {
        if ($this->profile !== $profile) {
            $this->onPropertyChanged('profile', $this->profile, $profile);
            $this->profile = $profile;
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
     * @param string $method
     * @param array  $arguments
     */
    public function __call($method, $arguments)
    {
        $this->called[] = $method;
    }
    /**
     * @see HydratableInterface
     *
     * @param array                                           $data
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface $objectManager
     */
    public function hydrate(array $data, ObjectManagerInterface $objectManager)
    {
        if (isset($data['id'])) {
            $this->id = (int) $data['id'];
        }
        if (isset($data['username'])) {
            $this->username = (string) $data['username'];
        }
        if (isset($data['password'])) {
            $this->password = (string) $data['password'];
        }
        if (isset($data['profileId']) && isset($data['profileName'])) {
            $profileData = array(
                'id' => (int) $data['profileId'],
                'name' => $data['profileName'],
            );
            $this->profile = function () use ($objectManager, $profileData) {
                return $objectManager->getOrCreateObject(
                    'Doctrine\SkeletonMapper\Tests\Model\Profile',
                    $profileData
                );
            };
        } elseif (isset($data['profileId'])) {
            $this->profile = function () use ($objectManager, $data) {
                return $objectManager->find(
                    'Doctrine\SkeletonMapper\Tests\Model\Profile',
                    (int) $data['profileId']
                );
            };
        }
        if (isset($data['groupIds'])) {
            $this->groups = new LazyCollection(function () use ($objectManager, $data) {
                return new ArrayCollection(array_map(function ($groupId) use ($objectManager) {
                    return $objectManager->find(
                        'Doctrine\SkeletonMapper\Tests\Model\Group',
                        (int) $groupId
                    );
                }, explode(',', $data['groupIds'])));
            });
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
            'username' => $this->username,
            'password' => $this->password,
        );
        if ($this->profile !== null) {
            $changeSet['profileId'] = $this->profile->getId();
        }
        if ($this->groups) {
            $groupIds = $this->groups->map(function (Group $group) {
                return $group->getId();
            })->toArray();
            $changeSet['groupIds'] = implode(',', $groupIds);
        }
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
        if (isset($changeSet['profile'])) {
            $changeSet['profileId'] = $changeSet['profile']->getId();
            unset($changeSet['profile']);
        }
        if (isset($changeSet['groups'])) {
            $groupIds = $changeSet['groups']->map(function (Group $group) {
                return $group->getId();
            })->toArray();
            $changeSet['groupIds'] = implode(',', $groupIds);
            unset($changeSet['groups']);
        }
        return $changeSet;
    }
}
