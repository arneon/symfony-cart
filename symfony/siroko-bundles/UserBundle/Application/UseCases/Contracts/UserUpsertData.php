<?php

namespace UserBundle\Application\UseCases\Contracts;
interface UserUpsertData
{
    public function getName(): string;
    public function getEmail(): string;
    public function getPassword(): ?string;
    public function isEnabled(): ?bool;
}
