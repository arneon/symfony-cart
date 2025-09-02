<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

final class JwtUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct(
        private string $identifier,
        private array $roles = ['ROLE_USER']
    ) {}

    public function getUserIdentifier(): string { return $this->identifier; }
    public function getRoles(): array { return array_values(array_unique($this->roles)); }
    public function getPassword(): ?string { return null; }
    public function eraseCredentials(): void {}
}
