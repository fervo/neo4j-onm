<?php

namespace Models\TVModel;

use Fervo\ONM\Mapping\Annotations as ONM;

/**
* @ONM\Node(label="Award")
*/
class Award
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
     * @ONM\OutgoingRelationship(typeName="AWARDED_TO", constraints={@ONM\TargetType("TVShow")})
     */
    protected $awardedTo;
}
