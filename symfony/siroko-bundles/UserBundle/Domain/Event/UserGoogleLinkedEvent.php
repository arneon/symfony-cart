<?php

namespace UserBundle\Domain\Event;

use DateTimeImmutable;

class UserGoogleLinkedEvent implements DomainEvent
{
    public function __construct(
        private readonly string $userId,
        private readonly string $googleId,
        private readonly DateTimeImmutable $occurredAt = new DateTimeImmutable()
    ) {}

    public static function eventName(): string
    {
        return 'user.user_google_linked';
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function toArray(): array
    {
        return [
            'user_id'  => $this->userId,
            'google_id'=> $this->googleId,
        ];
    }
}

