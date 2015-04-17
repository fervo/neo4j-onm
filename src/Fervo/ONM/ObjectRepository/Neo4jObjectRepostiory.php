<?php

use Doctrine\SkeletonMapper\ObjectRepository\BasicObjectRepository;

class Neo4jObjectRepository extends BasicObjectRepository
{
    const RELATIONSHIP_OUTGOING = 1;
    const RELATIONSHIP_INCOMING = 2;

    public function findOneRelated($node, $relationship, $direction)
    {
        # code...
    }
}
