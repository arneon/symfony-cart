<?php

namespace UserBundle\Domain\Event;

use UserBundle\Domain\Model\User;
use UserBundle\Domain\ValueObject\UserId;

class RegisteredUserEvent implements DomainEvent
{
    private UserId $userId;
    private \DateTimeImmutable $occurredAt;
    public function __construct(UserId $userId)
    {
        $this->userId = $userId;
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public static function eventName(): string
    {
        return 'users.user_registered';
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId->value(),
            'occurred_at' => $this->occurredAt->format('Y-m-d H:i:s'),
        ];
    }
}
