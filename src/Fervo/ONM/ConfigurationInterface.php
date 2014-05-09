<?php

namespace Fervo\ONM;

interface ConfigurationInterface {
    public function getClassMetadataFactoryName();
    public function getMetadataDriverImpl();
    public function getMetadataCacheImpl();
    public function getProxyDir();
    public function getProxyNamespace();
    public function getAutoGenerateProxyClasses();
}
