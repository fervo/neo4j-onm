<?php

namespace Fervo\ONM\Mapping\Annotations;

use Doctrine\Common\Annotations\Annotation;

/** @Annotation */
final class Relationship extends Annotation
{
    public $name;
}
