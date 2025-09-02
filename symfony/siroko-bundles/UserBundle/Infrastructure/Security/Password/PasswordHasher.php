<?php

namespace UserBundle\Infrastructure\Security\Password;

use Symfony\Component\PasswordHasher\PasswordHasherInterface;

final class PasswordHasher implements PasswordHasherInterface
{
    public function __construct(
        private string|int $algorithm = PASSWORD_ARGON2ID,
        private array $options = [
            'memory_cost' => 1 << 17,
            'time_cost'   => 3,
            'threads'     => 2,
            ]
    ) {}

    public function hash(string $plainPassword): string
    {
        $hashedPassword = password_hash($plainPassword, $this->algorithm, $this->options);
        if ($hashedPassword === false) throw new \RuntimeException('Hashing failed');

        return $hashedPassword;
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return password_needs_rehash($hashedPassword, $this->algorithm, $this->options);
    }
}
