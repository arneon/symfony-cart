<?php

namespace UserBundle\Application\UseCases\LoginWithGoogle;

use UserBundle\Infrastructure\Security\Authentication\Google\GoogleIdTokenVerifier;
use UserBundle\Domain\Repository\UserRepository;
use UserBundle\Domain\Model\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use UserBundle\Infrastructure\Security\Password\PasswordHasher;

final readonly class LoginWithGoogleHandler
{
    public function __construct(
        private GoogleIdTokenVerifier $verifier,
        private UserRepository $users,
        private JWTTokenManagerInterface $jwt,
        private PasswordHasher $passwordHasher,
    ) {}

    public function __invoke(LoginWithGoogleCommand $cmd): array
    {
        $data = $this->verifier->verify($cmd->idToken);

        // 1) Buscar por googleId o por email
        $user = $this->users->findByGoogleId($data['googleId'])
            ?? $this->users->findByEmail($data['email']);

        // 2) Crear si no existe
        if (!$user) {
            $hashedRandomPassword = $this->passwordHasher->hash('random1234567890');
            $user = User::registerWithGoogle(
                name: new UserName($data['name'] ?? 'Usuario Google'),
                email: new UserEmail($data['email']),
                passwordPlaceholder: new UserPassword($hashedRandomPassword),
                googleId: $data['googleId'],
                roles: ['ROLE_USER'],
            );
            $this->users->save($user);
        } else {
            // 3) Vincular Google si aún no está
            if (!$user->googleId()) {
                $user->linkGoogle($data['googleId']);
            }
            // Opcional: refrescar nombre/foto si cambian
            $user->refreshFromGoogle($data['name'] ?? null, $data['picture'] ?? null);
            $this->users->save($user);
        }

        // 4) Emitir JWT propio
        $token = $this->jwt->create($user);

        return [
            'token'       => $token,
            'token_type'  => 'Bearer',
            // si quieres devolver expiración, lee la TTL de Lexik o calcula exp:
            // 'expires_in' => 3600,
            'user' => [
                'id'    => (string)$user->id(),
                'email' => $user->email(),
                'name'  => $user->name(),
                'roles' => $user->roles(),
                'picture' => $user->picture(),
            ],
        ];
    }
}

