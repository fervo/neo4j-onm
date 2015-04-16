<?php

namespace Fervo\ONM\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\EventManager;
use Doctrine\SkeletonMapper\DataRepository\ArrayObjectDataRepository;
use Doctrine\SkeletonMapper\Hydrator\BasicObjectHydrator;
use Doctrine\SkeletonMapper\Mapping\ClassMetadata;
use Fervo\ONM\Mapping\ClassMetadataFactory;
use Doctrine\SkeletonMapper\ObjectFactory;
use Doctrine\SkeletonMapper\ObjectIdentityMap;
use Doctrine\SkeletonMapper\ObjectManager;
use Doctrine\SkeletonMapper\ObjectRepository\BasicObjectRepository;
use Doctrine\SkeletonMapper\Persister\ArrayObjectPersister;
use Doctrine\SkeletonMapper\Persister\ObjectPersisterFactory;
use Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryFactory;
use Models\User;
use Fervo\ONM\ConfigurationInterface;
use Fervo\ONM\Mapping\Driver\AnnotationDriver;
use Fervo\ONM\DataRepository\Neo4jObjectDataRepository;
use Neo4j\Neo4jPDO;
use Fervo\ONM\Persister\Neo4jObjectPersister;

/**
* 
*/
class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    protected $objectManager;
    protected $conn;

    public function setUp()
    {
        $eventManager            = new EventManager();

        $driver = AnnotationDriver::create();
        $classMetadataFactory    = new ClassMetadataFactory($eventManager, $driver);
        $classMetadataFactory->setCacheDriver(new \Doctrine\Common\Cache\ArrayCache);

        $objectFactory           = new ObjectFactory();
        $objectRepositoryFactory = new ObjectRepositoryFactory();
        $objectPersisterFactory  = new ObjectPersisterFactory();
        $objectIdentityMap       = new ObjectIdentityMap(
            $objectRepositoryFactory,
            $classMetadataFactory
        );

        $objectManager = new ObjectManager(
            $objectRepositoryFactory,
            $objectPersisterFactory,
            $objectIdentityMap,
            $classMetadataFactory,
            $eventManager
        );

        $conn = new Neo4jPDO("http://192.168.42.43:7474");

        $userDataRepository = new Neo4jObjectDataRepository(
            $objectManager, $conn, Users::class, 'User'
        );

        $userPersister = new Neo4jObjectPersister(
            $objectManager, $conn, Users::class, 'User'
        );

        $userHydrator = new BasicObjectHydrator($objectManager);
        $userRepository = new BasicObjectRepository(
            $objectManager,
            $userDataRepository,
            $objectFactory,
            $userHydrator,
            $eventManager,
            'Models\User'
        );

        $objectRepositoryFactory->addObjectRepository('Models\User', $userRepository);
        $objectPersisterFactory->addObjectPersister('Models\User', $userPersister);

        $this->objectManager = $objectManager;
        $this->conn = $conn;
    }

    public function testSomething()
    {
        $this->conn->exec('MATCH (n:User) DELETE n');

        // create and persist a new user
        $user = new User();
        $user->setId(1);
        $user->setUsername('jwage');
        $user->setPassword('test');

        $this->objectManager->persist($user);
        $this->objectManager->flush();

        $truth = $this->conn->query('MATCH (n:User) WHERE n.id = 1 RETURN n')->fetchAll();

        $this->assertArrayHasKey(0, $truth);
        $this->assertArrayHasKey('n', $truth[0]);
        $this->assertEquals($truth[0]['n']['username'], 'jwage');
        $this->assertEquals($truth[0]['n']['password'], 'test');

        // modify the user
        $user->setUsername('jonwage');

        $this->objectManager->flush();

        $truth = $this->conn->query('MATCH (n:User) WHERE n.id = 1 RETURN n')->fetchAll();

        $this->assertArrayHasKey(0, $truth);
        $this->assertArrayHasKey('n', $truth[0]);
        $this->assertEquals($truth[0]['n']['username'], 'jonwage');
        $this->assertEquals($truth[0]['n']['password'], 'test');

        // remove the user
        $this->objectManager->remove($user);
        $this->objectManager->flush();

        $truth = $this->conn->query('MATCH (n:User) WHERE n.id = 1 RETURN n')->fetchAll();

        $this->assertArrayNotHasKey(0, $truth);
    }
}
