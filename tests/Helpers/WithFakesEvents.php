<?php


namespace Tests\Helpers;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;

trait WithFakesEvents
{
    /**
     * @var EventFake
     */
    private $eventFake;

    protected function fakesEvents($eventsToFake = [])
    {
        $this->eventFake = new EventFake(Event::getFacadeRoot(), $eventsToFake);

        Event::swap($this->eventFake);

        Model::setEventDispatcher($this->eventFake);
    }

    protected function assertDispatched($event)
    {
        return $this->eventFake->assertDispatched($event);
    }

    protected function assertNotDispatched($event)
    {
        return $this->eventFake->assertNotDispatched($event);
    }
}