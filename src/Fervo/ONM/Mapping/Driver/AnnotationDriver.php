<?php

namespace Fervo\ONM\Mapping\Driver;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver as AbstractAnnotationDriver;
use Fervo\ONM\Mapping\Annotations as ONM;
use Fervo\ONM\Mapping\ClassMetadataInfo;
//use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;
//use Doctrine\ODM\MongoDB\Mapping\MappingException;

/**
 * The AnnotationDriver reads the mapping metadata from docblock annotations.
 *
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Roman Borschel <roman@code-factory.org>
 */
class AnnotationDriver extends AbstractAnnotationDriver
{
    protected $entityAnnotationClasses = array(
        'Fervo\\ONM\\Mapping\\Annotations\\Node' => 1,
        'Fervo\\ONM\\Mapping\\Annotations\\Relationship' => 2
    );

    /**
     * Registers annotation classes to the common registry.
     *
     * This method should be called when bootstrapping your application.
     */
    public static function registerAnnotationClasses()
    {
        AnnotationRegistry::registerFile(__DIR__ . '/../Annotations/DoctrineAnnotations.php');
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $class)
    {
        /** @var $class ClassMetadataInfo */
        $reflClass = $class->getReflectionClass();

        $classAnnotations = $this->reader->getClassAnnotations($reflClass);

        $documentAnnots = array();
        foreach ($classAnnotations as $annot) {
            $classAnnotations[get_class($annot)] = $annot;

            foreach ($this->entityAnnotationClasses as $annotClass => $i) {
                if ($annot instanceof $annotClass) {
                    $documentAnnots[$i] = $annot;
                    continue 2;
                }
            }

        }

        if ( ! $documentAnnots) {
            throw MappingException::classIsNotAValidDocument($className);
        }

        // find the winning document annotation
        ksort($documentAnnots);
        $documentAnnot = reset($documentAnnots);

        if ($documentAnnot instanceof ONM\Relationship) {
            $class->setClassIsRelationship($documentAnnot->name);
        } elseif ($documentAnnot instanceof ONM\Node) {
            $class->setClassIsNode($documentAnnot->label);
        } else {
            throw MappingException::classIsNotAValidDocument($className);
        }

        foreach ($reflClass->getProperties() as $property) {
            $mapping = array('fieldName' => $property->getName());

            foreach ($this->reader->getPropertyAnnotations($property) as $annot) {
                if ($annot instanceof ONM\Property) {
                    $mapping = array_replace($mapping, (array) $annot);

                    $class->mapField($mapping);
                } elseif ($annot instanceof ONM\AbstractRelationship) {
                    $mapping['direction'] = $annot instanceof ONM\IncomingRelationship ?
                        ClassMetadataInfo::RELATIONSHIP_DIRECTION_INCOMING :
                        ClassMetadataInfo::RELATIONSHIP_DIRECTION_OUTGOING;

                    if ($annot->typeName) {
                        $mapping['typeName'] = $annot->typeName;
                    } else {
                        $mapping['typeClass'] = $annot->typeClass;
                    }

                    $mapping['toMany'] = true;
                    foreach ($annot->constraints as $constraint) {
                        if ($constraint instanceof ONM\TargetType) {
                            $mapping['targetEntity'] = $constraint->value;
                        } elseif ($constraint instanceof ONM\Cardinality) {
                            $mapping['toMany'] = $constraint->value !== 1;
                        }
                    }

                    $class->mapRelationship($mapping);
                } elseif ($annot instanceof ONM\Id) {
                    $class->mapIdentifier($property->getName());
                }
            }
        }
    }

    /**
     * Factory method for the Annotation Driver
     *
     * @param array|string $paths
     * @param Reader $reader
     * @return AnnotationDriver
     */
    public static function create($paths = array(), Reader $reader = null)
    {
        if ($reader === null) {
            $reader = new AnnotationReader();
        }
        return new self($reader, $paths);
    }
}
