<?php

namespace UserBundle\Infrastructure\Security\Authentication\Google;

use Google_Client;
use UserBundle\Application\Ports\Security\{IdTokenVerifier, SocialProfile};
use RuntimeException;

final readonly class GoogleIdTokenVerifier implements IdTokenVerifier
{
    public function __construct(private string $clientId) {}

    public function verify(string $idToken): SocialProfile
    {
        $client = new Google_Client(['client_id' => $this->clientId]);
        $payload = $client->verifyIdToken($idToken);
        if (!$payload) {
            throw new RuntimeException('Invalid Google ID token.');
        }
        $iss = $payload['iss'] ?? '';
        if (!in_array($iss, ['accounts.google.com', 'https://accounts.google.com'], true)) {
            throw new RuntimeException('Invalid issuer.');
        }
        if (($payload['aud'] ?? null) !== $this->clientId) {
            throw new RuntimeException('Invalid audience.');
        }
        if (empty($payload['sub']) || empty($payload['email'])) {
            throw new RuntimeException('Token missing subject/email.');
        }

        return new SocialProfile(
            provider: 'google',
            subject:  $payload['sub'],
            email:    $payload['email'],
            emailVerified: (bool)($payload['email_verified'] ?? false),
            name:     $payload['name']   ?? null,
            picture:  $payload['picture'] ?? null
        );
    }
}

