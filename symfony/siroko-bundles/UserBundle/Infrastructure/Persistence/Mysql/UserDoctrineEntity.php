<?php

namespace UserBundle\Infrastructure\Persistence\Mysql;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class UserDoctrineEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 100)]
    private string $userName;

    #[ORM\Column(name: 'email', type: 'string', length: 100, unique: true)]
    private string $userEmail;

    #[ORM\Column(name: 'password', type: 'string', length: 100)]
    private string $userPassword;

    #[ORM\Column(name: 'roles', type: 'json')]
    private array $roles = ['ROLE_USER'];

    #[ORM\Column(name: 'google_id', type: 'string', length: 64, nullable: true)]
    private ?string $googleId = null;

    #[ORM\Column(name: 'enabled', type: Types::BOOLEAN, options: ['default' => true])]
    private bool $userEnabled = true;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): void
    {
        $this->userName = $userName;
    }

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }

    public function setUserEmail(string $userEmail): void
    {
        $this->userEmail = $userEmail;
    }

    public function getPassword(): string
    {
        return $this->userPassword;
    }

    public function setPassword(string $userPassword): void
    {
        $this->userPassword = $userPassword;
    }

    public function isEnabled(): bool
    {
        return $this->userEnabled;
    }

    public function setEnabled(bool $userEnabled): void
    {
        $this->userEnabled = $userEnabled;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUserIdentifier(): string
    {
        return $this->userEmail;
    }
    public function getRoles(): array
    {
        $roles = $this->roles ?: ['ROLE_USER'];
        return array_values(array_unique($roles));
    }
    public function setRoles(array $roles): void
    {
        $this->roles = array_values(array_unique($roles ?: ['ROLE_USER']));
    }
    public function eraseCredentials(): void
    {

    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): void
    {
        $this->googleId = $googleId;
    }

}
