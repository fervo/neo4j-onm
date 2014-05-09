<?php

namespace Models\TVModel;

use Fervo\ONM\Mapping\Annotations as ONM;

/**
* @ONM\Node(label="Network")
*/
class Network
{
    /**
     * @ONM\Id
     */
    protected $id;

    /**
     * @ONM\Property(type="string")
     */
    protected $name;

    /**
     * @ONM\IncomingRelationship(typeName="RUNS_ON_NETWORK", constraints={@ONM\TargetType("TVShow")})
     */
    protected $shows;

    /**
     * @ONM\OutgoingRelationship(typeClass="HasCEORelation", constraints={@ONM\TargetType("Person")})
     */
    protected $ceo;
}
