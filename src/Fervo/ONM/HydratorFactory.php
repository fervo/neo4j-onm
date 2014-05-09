<?php

namespace Fervo\ONM;

use GeneratedHydrator\Configuration as HydratorConfig;

/**
* 
*/
class HydratorFactory
{
    protected $hydrators = [];

    public function getHydratorFor($className)
    {
        if (isset($this->hydrators[$className])) {
            return $this->hydrators[$className];
        }

        $config = new HydratorConfig($className);
        $hydratorClass = $config->createFactory()->getHydratorClass();
        $this->hydrators[$className] = new $hydratorClass();

        return $this->hydrators[$className];
    }
}
