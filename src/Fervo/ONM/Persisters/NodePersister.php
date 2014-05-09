<?php

namespace Fervo\ONM\Persisters;

use Fervo\ONM\Mapping\ClassMetadataFactory;

use Neo4j\Neo4jPDO;
use PDO;

/**
* 
*/
class NodePersister
{
    protected $conn;

    protected $cmd;

    public function __construct(Neo4jPDO $conn, ClassMetadataFactory $cmd)
    {
        $this->conn = $conn;
        $this->cmd = $cmd;
    }


}
