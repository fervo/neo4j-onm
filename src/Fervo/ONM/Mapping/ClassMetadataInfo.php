<?php

namespace Fervo\ONM\Mapping;

use BadMethodCallException;
use InvalidArgumentException;
use RuntimeException;
use ReflectionClass;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\ClassLoader;

/**
 * A <tt>ClassMetadata</tt> instance holds all the object-relational mapping metadata
 * of an entity and its associations.
 *
 * Once populated, ClassMetadata instances are usually cached in a serialized form.
 *
 * <b>IMPORTANT NOTE:</b>
 *
 * The fields of this class are only public for 2 reasons:
 * 1) To allow fast READ access.
 * 2) To drastically reduce the size of a serialized instance (private/protected members
 *    get the whole class name, namespace inclusive, prepended to every property in
 *    the serialized representation).
 *
 * @author Roman Borschel <roman@code-factory.org>
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @since 2.0
 */
class ClassMetadataInfo implements ClassMetadata
{
    const CLASS_TYPE_NODE = 1;
    const CLASS_TYPE_RELATIONSHIP = 2;

    const RELATIONSHIP_DIRECTION_INCOMING = 1;
    const RELATIONSHIP_DIRECTION_OUTGOING = 2;

    /**
     * READ-ONLY: The name of the entity class.
     *
     * @var string
     */
    public $name;

    /**
     * READ-ONLY: The namespace the entity class is contained in.
     *
     * @var string
     *
     * @todo Not really needed. Usage could be localized.
     */
    public $namespace;

    /**
     * READ-ONLY: The field names of all fields that are part of the identifier/primary key
     * of the mapped entity class.
     *
     * @var array
     */
    public $identifier = array();

    /**
     * READ-ONLY: The field mappings of the class.
     * Keys are field names and values are mapping definitions.
     *
     * The mapping definition array has the following values:
     *
     * - <b>fieldName</b> (string)
     * The name of the field in the Entity.
     *
     * - <b>type</b> (string)
     * The type name of the mapped field. Can be one of Doctrine's mapping types
     * or a custom mapping type.
     *
     * - <b>columnName</b> (string, optional)
     * The column name. Optional. Defaults to the field name.
     *
     * @var array
     */
    public $fieldMappings = array();

    /**
     * READ-ONLY: The primary table definition. The definition is an array with the
     * following entries:
     *
     * type => node|relationship
     * relationshipName => <relationshipName> (if type=relationship)
     * label => <label> (if type=node)
     *
     * @var array
     */
    public $classDefinition;

    /**
     * READ-ONLY: The association mappings of this class.
     *
     * The mapping definition array supports the following keys:
     *
     * - <b>fieldName</b> (string)
     * The name of the field in the entity the association is mapped to.
     *
     * - <b>targetEntity</b> (string)
     * The class name of the target entity. If it is fully-qualified it is used as is.
     * If it is a simple, unqualified class name the namespace is assumed to be the same
     * as the namespace of the source entity.
     *
     * - <b>direction</b>
     * The relationship direction
     *
     * - <b>typeName</b> (string, required for non-object relationships)
     * The relationship name.
     *
     * - <b>typeClass</b> (string, required for object relationships)
     * The class name of the relationship class.
     *
     * - <b>toMany</b> (boolean)
     * Whether the relationship should be mapped as an array or not.
     *
     * @var array
     */
    public $associationMappings = array();

    /**
     * The ReflectionClass instance of the mapped class.
     *
     * @var ReflectionClass
     */
    public $reflClass;

    /**
     * The ReflectionProperty instances of the mapped class.
     *
     * @var \ReflectionProperty[]
     */
    public $reflFields = array();

    /**
     * The prototype from which new instances of the mapped class are created.
     *
     * @var object
     */
    private $_prototype;

    /**
     * Initializes a new ClassMetadata instance that will hold the object-relational mapping
     * metadata of the class with the given name.
     *
     * @param string              $entityName     The name of the entity class the new instance is used for.
     */
    public function __construct($entityName)
    {
        $this->name = $entityName;
    }

    public function mapIdentifier($fieldName)
    {
        $this->identifier[0] = $fieldName;
        $this->mapField([
            'fieldName' => $fieldName,
            'type' => 'int',
        ]);
    }

    public function mapField(array $mapping)
    {
        $this->fieldMappings[$mapping['fieldName']] = [
            'fieldName' => $mapping['fieldName'],
            'type' => $mapping['type'],
            'propertyName' => $mapping['fieldName'],
        ];
    }

    public function mapRelationship(array $mapping)
    {
        $this->associationMappings[$mapping['fieldName']] = [
            'fieldName' => $mapping['fieldName'],
            'targetEntity' => $mapping['targetEntity'],
            'direction' => $mapping['direction'],
            'toMany' => $mapping['toMany'],
        ];

        if (isset($mapping['typeName'])) {
            $this->associationMappings[$mapping['fieldName']]['typeName'] = $mapping['typeName'];
        } elseif (isset($mapping['typeClass'])) {
            $this->associationMappings[$mapping['fieldName']]['typeClass'] = $mapping['typeClass'];
        }
    }

    public function setClassIsRelationship($relationshipName)
    {
        $this->classDefinition = [
            'type' => self::CLASS_TYPE_RELATIONSHIP,
            'relationshipName' => $relationshipName,
        ];
    }

    public function setClassIsNode($label)
    {
        $this->classDefinition = [
            'type' => self::CLASS_TYPE_NODE,
            'label' => $label,
        ];
    }

    /**
     * Gets the ReflectionProperties of the mapped class.
     *
     * @return array An array of ReflectionProperty instances.
     */
    public function getReflectionProperties()
    {
        return $this->reflFields;
    }

    /**
     * Gets a ReflectionProperty for a specific field of the mapped class.
     *
     * @param string $name
     *
     * @return \ReflectionProperty
     */
    public function getReflectionProperty($name)
    {
        return $this->reflFields[$name];
    }

    /**
     * Gets the ReflectionProperty for the single identifier field.
     *
     * @return \ReflectionProperty
     *
     * @throws BadMethodCallException If the class has a composite identifier.
     */
    public function getSingleIdReflectionProperty()
    {
        return $this->reflFields[$this->identifier[0]];
    }

    /**
     * Extracts the identifier values of an entity of this class.
     *
     * For composite identifiers, the identifier values are returned as an array
     * with the same order as the field order in {@link identifier}.
     *
     * @param object $entity
     *
     * @return array
     */
    public function getIdentifierValues($entity)
    {
        $id = $this->identifier[0];
        $value = $this->reflFields[$id]->getValue($entity);

        if (null === $value) {
            return array();
        }

        return array($id => $value);
    }

    /**
     * Sets the specified field to the specified value on the given entity.
     *
     * @param object $entity
     * @param string $field
     * @param mixed  $value
     *
     * @return void
     */
    public function setFieldValue($entity, $field, $value)
    {
        $this->reflFields[$field]->setValue($entity, $value);
    }

    /**
     * Gets the specified field's value off the given entity.
     *
     * @param object $entity
     * @param string $field
     *
     * @return mixed
     */
    public function getFieldValue($entity, $field)
    {
        return $this->reflFields[$field]->getValue($entity);
    }

    /**
     * Creates a string representation of this instance.
     *
     * @return string The string representation of this instance.
     *
     * @todo Construct meaningful string representation.
     */
    public function __toString()
    {
        return __CLASS__ . '@' . spl_object_hash($this);
    }

    /**
     * Determines which fields get serialized.
     *
     * It is only serialized what is necessary for best unserialization performance.
     * That means any metadata properties that are not set or empty or simply have
     * their default value are NOT serialized.
     *
     * Parts that are also NOT serialized because they can not be properly unserialized:
     *      - reflClass (ReflectionClass)
     *      - reflFields (ReflectionProperty array)
     *
     * @return array The names of all the fields that should be serialized.
     */
    public function __sleep()
    {
        // This metadata is always serialized/cached.
        $serialized = array(
            'associationMappings',
            'fieldMappings',
            'identifier',
            'name',
            'namespace', // TODO: REMOVE
            'table',
        );

        if ($this->cache) {
            $serialized[] = 'cache';
        }

        return $serialized;
    }

    /**
     * Creates a new instance of the mapped class, without invoking the constructor.
     *
     * @return object
     */
    public function newInstance()
    {
        if ($this->_prototype === null) {
            $this->_prototype = unserialize(sprintf('O:%d:"%s":0:{}', strlen($this->name), $this->name));
        }

        return clone $this->_prototype;
    }
    /**
     * Restores some state that can not be serialized/unserialized.
     *
     * @param \Doctrine\Common\Persistence\Mapping\ReflectionService $reflService
     *
     * @return void
     */
    public function wakeupReflection($reflService)
    {
        // Restore ReflectionClass and properties
        $this->reflClass = $reflService->getClass($this->name);

        foreach ($this->fieldMappings as $field => $mapping) {
            $this->reflFields[$field] = $reflService->getAccessibleProperty($this->name, $field);
        }

        foreach ($this->associationMappings as $field => $mapping) {
            $this->reflFields[$field] = $reflService->getAccessibleProperty($this->name, $field);
        }
    }

    /**
     * Initializes a new ClassMetadata instance that will hold the object-relational mapping
     * metadata of the class with the given name.
     *
     * @param \Doctrine\Common\Persistence\Mapping\ReflectionService $reflService The reflection service.
     *
     * @return void
     */
    public function initializeReflection($reflService)
    {
        $this->reflClass = $reflService->getClass($this->name);
        $this->namespace = $reflService->getClassNamespace($this->name);

        if ($this->reflClass) {
            $this->name = $this->rootEntityName = $this->reflClass->getName();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getReflectionClass()
    {
        return $this->reflClass;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifierFieldNames()
    {
        return $this->identifier;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Checks whether a field is part of the identifier/primary key field(s).
     *
     * @param string $fieldName The field name.
     *
     * @return boolean TRUE if the field is part of the table identifier/primary key field(s),
     *                 FALSE otherwise.
     */
    public function isIdentifier($fieldName)
    {
        if ( ! $this->identifier) {
            return false;
        }

        return $fieldName === $this->identifier[0];
    }

    /**
     * Gets the type of a field.
     *
     * @param string $fieldName
     *
     * @return \Doctrine\DBAL\Types\Type|string|null
     */
    public function getTypeOfField($fieldName)
    {
        return isset($this->fieldMappings[$fieldName]) ?
                $this->fieldMappings[$fieldName]['type'] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function hasField($fieldName)
    {
        return isset($this->fieldMappings[$fieldName]);
    }

    /**
     * {@inheritDoc}
     */
    public function hasAssociation($fieldName)
    {
        return isset($this->associationMappings[$fieldName]);
    }

    /**
     * {@inheritDoc}
     */
    public function isSingleValuedAssociation($fieldName)
    {
        return isset($this->associationMappings[$fieldName]) &&
                ! ($this->associationMappings[$fieldName]['toMany']);
    }

    /**
     * {@inheritDoc}
     */
    public function isCollectionValuedAssociation($fieldName)
    {
        return isset($this->associationMappings[$fieldName]) &&
                ($this->associationMappings[$fieldName]['toMany']);
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldNames()
    {
        return array_keys($this->fieldMappings);
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationNames()
    {
        return array_keys($this->associationMappings);
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     */
    public function getAssociationTargetClass($assocName)
    {
        if ( ! isset($this->associationMappings[$assocName])) {
            throw new InvalidArgumentException("Association name expected, '" . $assocName ."' is not an association.");
        }

        return $this->associationMappings[$assocName]['targetEntity'];
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function isAssociationInverseSide($fieldName)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationMappedByTargetField($fieldName)
    {
        return null;
    }

    /**
     * @param   string $className
     * @return  string
     */
    public function fullyQualifiedClassName($className)
    {
        if ($className !== null && strpos($className, '\\') === false && strlen($this->namespace) > 0) {
            return $this->namespace . '\\' . $className;
        }

        return $className;
    }
}
