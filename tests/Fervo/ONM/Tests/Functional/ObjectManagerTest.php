<?php

namespace Fervo\ONM\Tests\Functional;

use Fervo\ONM\ConfigurationInterface;
use Fervo\ONM\Mapping\ClassMetadataFactory;
use Fervo\ONM\Mapping\Driver\AnnotationDriver;
use Fervo\ONM\ObjectManager;
use Doctrine\Common\EventManager;
use Neo4j\Neo4jPDO;

use Models\TVModel\TVShow;
use Fervo\ONM\Mapping\ClassMetadataInfo;

class ObjectManagerTest
{
    private $prophet;

    public function testFoo()
    {
        $om = $this->getObjectManager();
        $md = $om->getClassMetadata(TVShow::class);
        $this->assertInstanceOf(ClassMetadataInfo::class, $md);
    }

    public function testLoadNodeById()
    {
        $om = $this->getObjectManager();
//        $show = $om->find(TVShow::class, 13);

//        var_dump($show);
    }

    protected function setup()
    {
        $this->prophet = new \Prophecy\Prophet;
    }

    protected function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    protected function getConfigurationMock()
    {
        $config = $this->prophet->prophesize(ConfigurationInterface::class);
        $config->getClassMetadataFactoryName()->willReturn(ClassMetadataFactory::class);
        $config->getMetadataDriverImpl()->willReturn(AnnotationDriver::create());
        $config->getMetadataCacheImpl()->willReturn(new \Doctrine\Common\Cache\ArrayCache);
        $config->getProxyDir()->willReturn('/Users/magnus/Temp/onmproxies/');
        $config->getProxyNamespace()->willReturn('Proxies\ONM');
        $config->getAutoGenerateProxyClasses()->willReturn(true);

        return $config->reveal();
    }

    protected function getObjectManager()
    {
        $em = new EventManager();
        $conn = new Neo4jPDO("http://localhost:7474");
        $om = new ObjectManager($conn, $this->getConfigurationMock(), $em);

        return $om;
    }
}
