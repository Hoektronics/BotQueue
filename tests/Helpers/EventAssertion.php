<?php

namespace Tests\Helpers;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Assert;
use SebastianBergmann\Exporter\Exporter;

class EventAssertion
{
    /**
     * @var array
     */
    private $events;

    /**
     * @var Exporter
     */
    private $exporter;

    /**
     * EventAssertion constructor.
     * @param $events
     */
    public function __construct($events)
    {
        $this->events = Arr::wrap($events);
        $this->exporter = new Exporter;
    }

    public function inspect($callback)
    {
        $message = "We haven't written the code to inspect multiple events fired";
        $this->guardMultipleEvents($message);

        return $this->handleEventCallback($callback, $this->events[0]);
    }

    public function channels($channels)
    {
        $this->guardMultipleEvents("We haven't written the code to compare channels for multiple events fired");

        $arguments = $this->events[0];

        $this->guardEmptyArguments($arguments);

        $event = $arguments[0];
        $eventClass = get_class($event);

        $channels = Arr::wrap($channels);

        if (! $event instanceof ShouldBroadcast) {
            Assert::fail("Event [$eventClass] does not implement ShouldBroadcast interface");
        }

        $broadcastNames = collect($event->broadcastOn())->map(function ($channel) {
            /* @var $channel Channel */
            if(is_a($channel, Channel::class)) {
                return $channel->name;
            }
            return $channel;
        })->values()->all();

        $constraint = new SubsetConstraint($channels);
        Assert::assertThat($broadcastNames, $constraint);
    }

    protected function handleEventCallback($callback, $arguments)
    {
        $this->guardEmptyArguments($arguments);

        $event = $arguments[0];
        $eventClass = get_class($event);

        $initialAssertionCount = Assert::getCount();

        $result = $callback(...$arguments);

        if ($result === null) {
            $finalAssertionCount = Assert::getCount();

            if ($initialAssertionCount === $finalAssertionCount) {
                Assert::fail("No assertions were made on inspection of [$eventClass]");
            }

            return $this;
        }

        if (is_bool($result)) {
            Assert::assertTrue($result, "Inspection of event [$eventClass] returned false");
        }

        return $this;
    }

    /**
     * @param $arguments
     */
    protected function guardEmptyArguments($arguments)
    {
        if (count($arguments) === 0) {
            Assert::fail('Arguments to event callback must have at least the event itself');
        }
    }

    protected function guardMultipleEvents($message)
    {
        if (count($this->events) !== 1) {
            Assert::fail($message);
        }
    }
}
