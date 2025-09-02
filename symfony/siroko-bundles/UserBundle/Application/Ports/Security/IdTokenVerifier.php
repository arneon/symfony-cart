<?php

namespace UserBundle\Application\Ports\Security;

interface IdTokenVerifier
{
    public function verify(string $idToken): SocialProfile;
}
