<?php

namespace Models\TVModel;

use Fervo\ONM\Mapping\Annotations as ONM;

/**
* @ONM\Node(label="TVShow")
*/
class TVShow
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
     * @ONM\OutgoingRelationship(typeName="HAS_SEASON", constraints={@ONM\TargetType("Season")})
     */
    protected $seasons;

    /**
     * @ONM\OutgoingRelationship(typeName="RUNS_ON_NETWORK", constraints={@ONM\TargetType("Network"), @ONM\Cardinality(1)})
     */
    protected $network;

    /**
     * @ONM\IncomingRelationship(typeName="AWARDED_TO", constraints={@ONM\TargetType("Award")})
     */
    protected $awards;
}
