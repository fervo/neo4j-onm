<?php

namespace Models\TVModel;

use Fervo\ONM\Mapping\Annotations as ONM;

/**
* @ONM\Node(label="Person")
*/
class Person
{
    /**
     * @ONM\Id
     */
    protected $id;

    /**
     * @ONM\Property(type="string")
     */
    protected $name;
}
