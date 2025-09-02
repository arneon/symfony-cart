<?php

namespace UserBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use UserBundle\Infrastructure\Persistence\Mysql\UserDoctrineEntity;
use UserBundle\Infrastructure\Security\Password\PasswordHasher;

abstract class TestCase extends WebTestCase
{
    private PasswordHasher $passwordHasher;
    protected function setUp(): void
    {
        parent::setUp();
        $this->passwordHasher = new PasswordHasher();
    }
    protected function generateJwtToken(array $roles = ['ROLE_ADMIN']): string
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $hasher = $this->passwordHasher;

        $user = new UserDoctrineEntity();
        $user->setUserName('Test User');
        $user->setUserEmail('test_'.uniqid().'@mail.com');
        $user->setRoles($roles);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());
        $user->setPassword($hasher->hash('secret123'));

        $em->persist($user);
        $em->flush();

        $jwt = static::getContainer()->get(JWTTokenManagerInterface::class);

        return $jwt->create($user);
    }

    protected function authHeaders(array $roles = ['ROLE_ADMIN'], array $extra = []): array
    {
        $token = $this->generateJwtToken($roles);

        return array_merge([
            'HTTP_Authorization' => 'Bearer '.$token,
            'CONTENT_TYPE'       => 'application/json',
            'ACCEPT'             => 'application/json',
        ], $extra);
    }
}
