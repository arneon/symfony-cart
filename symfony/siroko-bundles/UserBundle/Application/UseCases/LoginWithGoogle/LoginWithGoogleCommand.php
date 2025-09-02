<?php

namespace UserBundle\Application\UseCases\LoginWithGoogle;

final readonly class LoginWithGoogleCommand
{
    public function __construct(public string $idToken) {}
}

