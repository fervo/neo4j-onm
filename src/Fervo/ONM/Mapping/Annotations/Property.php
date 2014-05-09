<?php

namespace Fervo\ONM\Mapping\Annotations;

use Doctrine\Common\Annotations\Annotation;

/** @Annotation */
final class Property extends Annotation
{
    public $type;
}
