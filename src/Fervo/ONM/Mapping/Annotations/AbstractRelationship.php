<?php

namespace Fervo\ONM\Mapping\Annotations;

use Doctrine\Common\Annotations\Annotation;

abstract class AbstractRelationship extends Annotation
{
    public $typeName;
    public $typeClass;
    public $constraints = array();
}
