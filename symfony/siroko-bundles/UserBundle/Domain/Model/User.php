<?php

namespace UserBundle\Domain\Model;


use UserBundle\Domain\Event\RegisteredUserEvent;
use UserBundle\Domain\Event\UserGoogleLinkedEvent;
use UserBundle\Domain\Event\UserProfileRefreshedFromGoogleEvent;
use UserBundle\Domain\Exception\ValidationException;
use UserBundle\Domain\ValueObject\UserId;
use UserBundle\Domain\ValueObject\UserName;
use UserBundle\Domain\ValueObject\UserEmail;
use UserBundle\Domain\ValueObject\UserPassword;
use UserBundle\Domain\Event\RegisteredUserEvent as Event;
use App\Traits\DomainEventsTrait;
use DateTimeImmutable;

class User
{
    use DomainEventsTrait;
    private ?UserId $userId = null;
    private ?bool $enabled = true;
    private ?string $googleId = null;
    private array $roles = ['ROLE_USER'];
    private array $domainEvents = [];
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    /**
     * @var array<object>
     */
    private array $events = [];

    public function __construct(
        private UserName $userName,
        private UserEmail $userEmail,
        private UserPassword $userPassword,
    )
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public static function registerWithGoogle(
        UserName $name,
        UserEmail $email,
        UserPassword $passwordPlaceholder,
        string $googleId,
        array $roles = ['ROLE_USER'],
    ): self {
        $googleId = trim($googleId);
        if ($googleId === '') {
            throw new ValidationException(['googleId cannot be empty']);
        }

        $self = new self($name, $email, $passwordPlaceholder);
        $self->googleId = $googleId;
        $self->setRoles($roles);

        return $self;
    }

    public function linkGoogle(string $googleId): void
    {
        $googleId = trim($googleId);
        if ($googleId === '') {
            throw new ValidationException(['googleId cannot be empty']);
        }

        if ($this->googleId !== null && $this->googleId !== $googleId) {
            throw new ValidationException(['User already linked to a different Google account.']);
        }

        if ($this->googleId === $googleId) {
            return; // idempotente
        }

        $this->googleId = $googleId;
        $this->touch();

        // Emitimos evento de vinculación (tiene sentido incluso si aún no hay ID, pero
        // normalmente ya lo habrá. Si todavía no, puedes mover este record al repo tras persistir).
        if ($this->userId) {
            $this->recordEvent(new UserGoogleLinkedEvent($this->userId, $this->googleId));
        }
    }

    public function refreshFromGoogle(?UserName $name): void
    {
        $changed = false;

        if ($name !== null && (string)$name !== $this->userName->value()) {
            $this->userName = $name;
            $changed = true;
        }

        if ($changed) {
            $this->touch();
            if ($this->userId) {
                $this->recordEvent(new UserProfileRefreshedFromGoogleEvent(
                    $this->userId,
                    $this->userName->value(),
                ));
            }
        }
    }

    public function getUserId(): ?int
    {
        return $this->userId?->value();
    }

    public function setUserId(UserId $userId): void
    {
        $this->userId = $userId;
        $this->recordEvent(new RegisteredUserEvent($this->userId));
    }

    public function getUserName(): UserName
    {
        return $this->userName;
    }

    public function setUserName(UserName $userName): void
    {
        $this->userName = $userName;
    }

    public function getUserEmail(): UserEmail
    {
        return $this->userEmail;
    }

    public function setUserEmail(UserEmail $userEmail): void
    {
        $this->userEmail = $userEmail;
    }

    public function getUserPassword(): UserPassword
    {
        return $this->userPassword;
    }

    public function setUserPassword(UserPassword $userPassword): void
    {
        $this->userPassword = $userPassword;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }


    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = array_values(array_unique($roles ?: ['ROLE_USER']));
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): void
    {
        $this->googleId = $googleId ? trim($googleId) : null;
        $this->touch();
    }
}
