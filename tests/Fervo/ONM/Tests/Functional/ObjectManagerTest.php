<?php

namespace Fervo\ONM\Tests\Functional;

use Fervo\ONM\ConfigurationInterface;
use Fervo\ONM\Mapping\ClassMetadataFactory;
use Fervo\ONM\Mapping\Driver\AnnotationDriver;
use Fervo\ONM\ObjectManager;
use Doctrine\Common\EventManager;

use Models\TVModel\TVShow;
use Fervo\ONM\Mapping\ClassMetadataInfo;

class ObjectManagerTest extends \PHPUnit_Framework_TestCase
{
    private $prophet;

    public function testFoo()
    {
        $em = new EventManager();
        $om = new ObjectManager(null, $this->getConfigurationMock(), $em);

        $md = $om->getClassMetadata(TVShow::class);
        $this->assertInstanceOf(ClassMetadataInfo::class, $md);
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
}
