<?php

namespace eBuildy\Component\EventDispatcher;

class Event
{
    /**
     * @var Boolean Whether no further event listeners should be triggered
     */
    public $propagationStopped = false;

    /**
     * @var string This event's name
     */
    public $name;

    /**
     * @var string This event's name
     */
    public $data;

    public function __construct($name, $data = null)
    {
        $this->name = $name;
        $this->data = $data;
    }

}