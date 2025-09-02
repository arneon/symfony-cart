<?php

namespace UserBundle\Application\UseCases\UpdateUser;

//use UserBundle\Domain\Event\RegisteredUserEvent;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use UserBundle\Application\UseCases\UpdateUser\UpdateUserCommand as Command;
use UserBundle\Application\UseCases\UpdateUser\UpdateUserValidator as Validator;
use UserBundle\Domain\Exception\ValidationException;
use UserBundle\Domain\Repository\UserRepository;
use UserBundle\Domain\Model\User;
use UserBundle\Domain\ValueObject\UserId;
use UserBundle\Domain\ValueObject\UserName;
use UserBundle\Domain\ValueObject\UserEmail;
use UserBundle\Domain\ValueObject\UserPassword;
use UserBundle\Infrastructure\Event\DomainEventDispatcher;
use UserBundle\Application\UseCases\Factory\DomainUserFactory;
use UserBundle\Infrastructure\Persistence\Mysql\UserDoctrineEntity;

class UpdateUserHandler
{
    public function __construct(
        private UserRepository $repository,
        private DomainEventDispatcher $eventDispatcher,
        private Validator $validator,
        private JWTTokenManagerInterface $jwtManager,
        private RefreshTokenManagerInterface $refreshManager,
        private EntityManagerInterface $em,
    ) {}

    public function __invoke(Command $command): void
    {
        try {
            $this->validator->validate($command);

            $userDoctrine = $this->repository->findByEmail(new UserEmail($command->email));

            $user = DomainUserFactory::fromCommand($command, $userDoctrine, $userDoctrine->getUserPassword()->value());
            $this->repository->save($user);

            $this->eventDispatcher->dispatchAll($user->pullDomainEvents());
        }catch(ValidationException $e) {
            throw new ValidationException($e->getErrors(), $e->getErrorCode());
        }
    }
}
