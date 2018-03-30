<?php


namespace Tests\Helpers;


use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Assert;

class EventFake implements Dispatcher
{

    /**
     * The original event dispatcher.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $dispatcher;

    /**
     * The event types that should be intercepted instead of dispatched.
     *
     * @var array
     */
    protected $eventsToFake;

    /**
     * All of the events that have been intercepted keyed by type.
     *
     * @var array
     */
    protected $events = [];

    /**
     * Create a new event fake instance.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher $dispatcher
     * @param  array|string $eventsToFake
     * @return void
     */
    public function __construct(Dispatcher $dispatcher, $eventsToFake = [])
    {
        $this->dispatcher = $dispatcher;

        $this->eventsToFake = Arr::wrap($eventsToFake);
    }

    public function hasDispatched($eventClass)
    {
        return isset($this->events[$eventClass]) && !empty($this->events[$eventClass]);
    }

    public function assertDispatched($eventClass)
    {
        Assert::assertTrue(
            $this->hasDispatched($eventClass),
            "Failed asserting that [{$eventClass}] was dispatched"
        );

        return new EventAssertion($this->events[$eventClass]);
    }

    public function assertNotDispatched($eventClass)
    {
        Assert::assertFalse(
            $this->hasDispatched($eventClass),
            "Failed asserting that [{$eventClass}] was not dispatched"
        );
    }

    /**
     * Determine if an event should be faked or actually dispatched.
     *
     * @param  string $eventName
     * @return bool
     */
    protected function shouldFakeEvent($eventName)
    {
        return empty($this->eventsToFake) || in_array($eventName, $this->eventsToFake);
    }

    /**
     * Dispatch an event until the first non-null response is returned.
     *
     * @param  string|object $event
     * @param  mixed $payload
     * @return array|null
     */
    public function until($event, $payload = [])
    {
        return $this->dispatch($event, $payload, true);
    }

    /**
     * Fire an event and call the listeners.
     *
     * @param  string|object $event
     * @param  mixed $payload
     * @param  bool $halt
     * @return array|null
     */
    public function fire($event, $payload = [], $halt = false)
    {
        return $this->dispatch($event, $payload, $halt);
    }

    /**
     * Dispatch an event and call the listeners.
     *
     * @param  string|object $event
     * @param  mixed $payload
     * @param  bool $halt
     * @return array|null
     */
    public function dispatch($event, $payload = [], $halt = false)
    {
        $name = is_object($event) ? get_class($event) : (string)$event;

        if ($this->shouldFakeEvent($name)) {
            $this->events[$name][] = func_get_args();
        } else {
            $this->dispatcher->dispatch($event, $payload, $halt);
        }
    }

    /**
     * Register an event listener with the dispatcher.
     *
     * @param  string|array $events
     * @param  mixed $listener
     * @return void
     */
    public function listen($events, $listener)
    {

    }

    /**
     * Determine if a given event has listeners.
     *
     * @param  string $eventName
     * @return bool
     */
    public function hasListeners($eventName)
    {

    }

    /**
     * Register an event subscriber with the dispatcher.
     *
     * @param  object|string $subscriber
     * @return void
     */
    public function subscribe($subscriber)
    {

    }

    /**
     * Register an event and payload to be fired later.
     *
     * @param  string $event
     * @param  array $payload
     * @return void
     */
    public function push($event, $payload = [])
    {

    }

    /**
     * Flush a set of pushed events.
     *
     * @param  string $event
     * @return void
     */
    public function flush($event)
    {

    }

    /**
     * Remove a set of listeners from the dispatcher.
     *
     * @param  string $event
     * @return void
     */
    public function forget($event)
    {

    }

    /**
     * Forget all of the queued listeners.
     *
     * @return void
     */
    public function forgetPushed()
    {

    }
}