<?php

namespace UserBundle\Application\UseCases\RegisterUser;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use UserBundle\Application\UseCases\Factory\DomainUserFactory;
use UserBundle\Domain\Exception\ValidationException;
use UserBundle\Domain\Repository\UserRepository;
use UserBundle\Domain\ValueObject\UserId;
use UserBundle\Infrastructure\Event\DomainEventDispatcher;
use UserBundle\Infrastructure\Persistence\Mysql\UserDoctrineEntity;
use UserBundle\Infrastructure\Security\Password\PasswordHasher;

class RegisterUserHandler
{
    public function __construct(
        private UserRepository $repository,
        private DomainEventDispatcher $eventDispatcher,
        private RegisterUserValidator $validator,
        private PasswordHasher $passwordHasher,
        private JWTTokenManagerInterface $jwtManager,
        private RefreshTokenManagerInterface $refreshManager,
        private EntityManagerInterface $em,
    ) {}

    public function __invoke(RegisterUserCommand $command): array
    {
        try {
            $this->validator->validate($command);

            $hash = $this->passwordHasher->hash($command->password);
            $user = DomainUserFactory::fromCommand($command, null, $hash);

            $id = $this->repository->save($user);
            $user->setUserId(new UserId($id));

            $securityUser = $this->em->getRepository(UserDoctrineEntity::class)
                ->findOneBy(['userEmail' => $user->getUserEmail()->value()]);

            $token = $this->jwtManager->create($securityUser);

            $refresh = $this->refreshManager->create();
            $refresh->setUsername($securityUser->getUserIdentifier());
            $refresh->setRefreshToken();
            $refresh->setValid(new DateTimeImmutable('+30 days'));
            $this->refreshManager->save($refresh);
            $expiresIn = 3600;

            $this->eventDispatcher->dispatchAll($user->pullDomainEvents());

            return [
                'token' => $token,
                'refresh_token' => $refresh->getRefreshToken(),
                'expires_in' => $expiresIn,
            ];

        }catch(ValidationException $e) {
            throw new ValidationException($e->getErrors(), $e->getErrorCode());
        }
    }
}
