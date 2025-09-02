<?php

namespace UserBundle\Application\UseCases\RegisterUser;

use UserBundle\Application\UseCases\Contracts\UserUpsertData;

final class RegisterUserCommand implements UserUpsertData
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly ?string $password = null,
        public readonly ?bool $enabled = true,
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }
}
