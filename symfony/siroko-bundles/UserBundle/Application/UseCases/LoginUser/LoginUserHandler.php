<?php

namespace UserBundle\Application\UseCases\LoginUser;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use UserBundle\Application\UseCases\LoginUser\LoginUserValidator as Validator;
use UserBundle\Domain\Exception\ValidationException;
use UserBundle\Domain\Repository\UserRepository;
use UserBundle\Domain\ValueObject\UserEmail;
use UserBundle\Infrastructure\Event\DomainEventDispatcher;
use UserBundle\Infrastructure\Persistence\Mysql\UserDoctrineEntity;
use UserBundle\Infrastructure\Security\Password\PasswordHasher;

class LoginUserHandler {
    public function __construct(
        private UserRepository $repository,
        private DomainEventDispatcher $eventDispatcher,
        private Validator $validator,
        private PasswordHasher $passwordHasher,
        private JWTTokenManagerInterface $jwtManager,
        private RefreshTokenManagerInterface $refreshManager,
        private EntityManagerInterface $em,
    )
    {
    }

    public function __invoke(LoginUserCommand $command): array
    {
        $this->validator->validate($command);

        $user = $this->repository->findByEmail(new UserEmail($command->email));
        if (!$user || !$this->passwordHasher->verify($user->getUserPassword()->value(), $command->password)) {
            throw new ValidationException(['Invalid credentials.'], 400);
        }

        try {
            $securityUser = $this->em->getRepository(UserDoctrineEntity::class)
                ->findOneBy(['userEmail' => $user->getUserEmail()->value()]);

            $token = $this->jwtManager->create($securityUser);
            $refresh = $this->refreshManager->getLastFromUsername($user->getUserEmail()->value());
            if($refresh)
            {
                $this->refreshManager->delete($refresh);
            }

            $refresh = $this->refreshManager->create();
            $refresh->setUsername($securityUser->getUserIdentifier());
            $refresh->setRefreshToken();
            $refresh->setValid(new DateTimeImmutable('+30 days'));
            $this->refreshManager->save($refresh);
            $expiresIn = 3600;

            return [
                'token' => $token,
                'refresh_token' => $refresh->getRefreshToken(),
                'expires_in' => $expiresIn,
            ];
        }catch (ValidationException $e) {
            throw new ValidationException([$e->getMessage()], $e->getCode());
        }
    }
}
