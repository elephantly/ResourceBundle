<?php 

namespace Elephantly\ResourceBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
* primary @author purplebabar(lalung.alexandre@gmail.com)
*/
class ResourceEvent extends Event
{

    protected $resource;

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    public function getResource()
    {
        return $this->resource;
    }
}