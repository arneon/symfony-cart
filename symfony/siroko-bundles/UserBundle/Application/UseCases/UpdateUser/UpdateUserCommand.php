<?php

namespace UserBundle\Application\UseCases\UpdateUser;

use UserBundle\Application\UseCases\Contracts\UserUpsertData;

class UpdateUserCommand implements UserUpsertData
{
    public ?int $id;
    public ?string $name;
    public ?string $email;
    public ?string $password;
    public ?bool $enabled;
    public ?array $roles;

    public function __construct(
        ?int $id = null,
        ?string $name = null,
        ?string $email = null,
        ?string $password = null,
        ?string $enabled = null,
        ?array $roles = [],
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->enabled = $enabled;
        $this->roles = $roles;
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
