<?php

if (!file_exists($file = __DIR__.'/../vendor/autoload.php')) {
    throw new RuntimeException('Install dependencies to run test suite.');
}

$loader = require $file;

$loader->add('Fervo\ONM\Tests', __DIR__ . '/../tests');
$loader->add('Models', __DIR__);

\Fervo\ONM\Mapping\Driver\AnnotationDriver::registerAnnotationClasses();
