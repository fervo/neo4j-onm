<?php

namespace Fervo\ONM\Mapping;

/**
 * Acts as a proxy to a nested Property structure, making it look like
 * just a single scalar property.
 *
 * This way value objects "just work" without UnitOfWork, Persisters or Hydrators
 * needing any changes.
 *
 * TODO: Move this class into Common\Reflection
 */
class ReflectionEmbeddedProperty
{
    private $parentProperty;
    private $childProperty;
    private $class;

    public function __construct($parentProperty, $childProperty, $class)
    {
        $this->parentProperty = $parentProperty;
        $this->childProperty = $childProperty;
        $this->class = $class;
    }

    public function getValue($object)
    {
        $embeddedObject = $this->parentProperty->getValue($object);

        if ($embeddedObject === null) {
            return null;
        }

        return $this->childProperty->getValue($embeddedObject);
    }

    public function setValue($object, $value)
    {
        $embeddedObject = $this->parentProperty->getValue($object);

        if ($embeddedObject === null) {
            $embeddedObject = unserialize(sprintf('O:%d:"%s":0:{}', strlen($this->class), $this->class));
            $this->parentProperty->setValue($object, $embeddedObject);
        }

        $this->childProperty->setValue($embeddedObject, $value);
    }
}
