<?php

namespace UserBundle\Domain\Event;

interface DomainEvent
{
    public static function eventName(): string;
    public function getOccurredAt(): \DateTimeImmutable;
    public function toArray(): array;
}
