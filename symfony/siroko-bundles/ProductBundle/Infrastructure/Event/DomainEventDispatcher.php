<?php

namespace ProductBundle\Infrastructure\Event;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DomainEventDispatcher
{
    public function __construct(private EventDispatcherInterface $dispatcher) {}

    public function dispatchAll(array $events): void
    {
        foreach ($events as $event) {
            $this->dispatcher->dispatch($event);
        }
    }
}
