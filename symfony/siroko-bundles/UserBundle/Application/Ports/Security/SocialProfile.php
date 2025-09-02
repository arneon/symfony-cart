<?php

namespace UserBundle\Application\Ports\Security;

final readonly class SocialProfile
{
    public function __construct(
        public string  $provider,
        public string  $subject,
        public string  $email,
        public bool    $emailVerified,
        public ?string $name = null,
        public ?string $picture = null,
    ) {}
}

