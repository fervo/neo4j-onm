<?php

namespace Fervo\ONM\Tests\Functional;

use Doctrine\Common\Persistence\Mapping\RuntimeReflectionService;
use Fervo\ONM\Configuration;
use Fervo\ONM\Mapping\Driver\AnnotationDriver;
use Fervo\ONM\Mapping\ClassMetadataInfo;

class AnnotationDriverTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadMetadataForNode()
    {
        $driver = AnnotationDriver::create();
        $reflectionService = new RuntimeReflectionService();

        $cmdi = new ClassMetadataInfo('Models\TVModel\TVShow');
        $cmdi->initializeReflection($reflectionService);

        $driver->loadMetadataForClass('Models\TVModel\TVShow', $cmdi);

        $this->assertEquals('Models\TVModel\TVShow', $cmdi->getName());
        $this->assertEquals(['id'], $cmdi->getIdentifier());
        $this->assertTrue($cmdi->isIdentifier('id'));
        $this->assertFalse($cmdi->isIdentifier('seasons'));
        $this->assertTrue($cmdi->hasField('name'));
        $this->assertFalse($cmdi->hasField('seasons'));
        $this->assertTrue($cmdi->hasAssociation('seasons'));
        $this->assertTrue($cmdi->hasAssociation('network'));
        $this->assertFalse($cmdi->hasAssociation('name'));
        $this->assertTrue($cmdi->isSingleValuedAssociation('network'));
        $this->assertFalse($cmdi->isSingleValuedAssociation('seasons'));
        $this->assertTrue($cmdi->isCollectionValuedAssociation('seasons'));
        $this->assertFalse($cmdi->isCollectionValuedAssociation('network'));
        $this->assertEquals(['id', 'name'], $cmdi->getFieldNames());
        $this->assertEquals(['id'], $cmdi->getIdentifierFieldNames());
        $this->assertEquals(['seasons', 'network', 'awards'], $cmdi->getAssociationNames());
        $this->assertEquals('string', $cmdi->getTypeOfField('name'));
        $this->assertEquals('Network', $cmdi->getAssociationTargetClass('network'));
        $this->assertFalse($cmdi->isAssociationInverseSide('network'));
        $this->assertFalse($cmdi->isAssociationInverseSide('seasons'));
        // We don't have inverse sides.
        $this->assertFalse($cmdi->isAssociationInverseSide('awards'));
        $this->assertNull($cmdi->getAssociationMappedByTargetField('awards'));
    }

    public function testLoadMetadataForRelationship()
    {
        $driver = AnnotationDriver::create();
        $reflectionService = new RuntimeReflectionService();

        $cmdi = new ClassMetadataInfo('Models\TVModel\HasCEORelation');
        $cmdi->initializeReflection($reflectionService);

        $driver->loadMetadataForClass('Models\TVModel\HasCEORelation', $cmdi);

        $this->assertEquals('Models\TVModel\HasCEORelation', $cmdi->getName());
        $this->assertEquals([], $cmdi->getIdentifier());
    }
}
