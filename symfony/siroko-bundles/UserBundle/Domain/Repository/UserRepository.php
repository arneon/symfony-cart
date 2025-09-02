<?php

namespace UserBundle\Domain\Repository;

use UserBundle\Domain\Model\User;
use UserBundle\Domain\ValueObject\UserId;
use UserBundle\Domain\ValueObject\UserEmail;

interface UserRepository
{
    public function findByEmail(UserEmail $email, int $exceptId = null): ?User;
    public function findByGoogleId(string $googleId): ?User;
    public function find(?UserId $id): ?User;
    public function save(User $user): int;
    public function delete(UserId $id): bool;
}
