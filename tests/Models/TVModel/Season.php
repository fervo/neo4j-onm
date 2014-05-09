<?php

namespace Models\TVModel;

use Fervo\ONM\Mapping\Annotations as ONM;

/**
* @ONM\Node(label="Season")
*/
class Season
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
     * @ONM\IncomingRelationship(typeName="HAS_SEASON", constraints={@ONM\TargetType("TVShow"), @ONM\Cardinality(1)})
     */
    protected $show;
}
