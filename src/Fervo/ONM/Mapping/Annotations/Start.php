<?php

namespace Fervo\ONM\Mapping\Annotations;

use Doctrine\Common\Annotations\Annotation;

/** @Annotation */
final class Start extends Annotation
{
    public $constraints;
}
