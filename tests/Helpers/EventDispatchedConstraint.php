<?php


namespace Tests\Helpers;


use PHPUnit\Framework\Constraint\Constraint;
use Tests\Helpers\EventFake;

class EventDispatchedConstraint extends Constraint
{
    /**
     * @var EventFake
     */
    private $eventFake;

    public function __construct(EventFake $eventFake)
    {
        parent::__construct();
        $this->eventFake = $eventFake;
    }

    protected function matches($other)
    {
        return $this->eventFake->hasDispatched($other);
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'was dispatched';
    }
}