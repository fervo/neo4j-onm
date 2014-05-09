<?php

namespace Fervo\ONM\Mapping\Annotations;

use Doctrine\Common\Annotations\Annotation;

/** @Annotation */
final class End extends Annotation
{
    public $constraints;
}
