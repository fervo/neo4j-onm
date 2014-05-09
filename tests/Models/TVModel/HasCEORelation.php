<?php

namespace Models\TVModel;

use Fervo\ONM\Mapping\Annotations as ONM;

/**
* @ONM\Relationship(name="HAS_CEO")
*/
class HasCEORelation
{
    /**
     * @ONM\Property(type="DateTime")
     */
    protected $since;

    /**
     * @ONM\Start(constraints={@ONM\TargetType("Network")})
     */
    protected $network;

    /**
     * @ONM\End(constraints={@ONM\TargetType("Person")})
     */
    protected $person;
}
