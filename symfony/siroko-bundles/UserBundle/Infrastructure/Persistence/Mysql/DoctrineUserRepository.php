<?php

namespace UserBundle\Infrastructure\Persistence\Mysql;

use Doctrine\ORM\EntityManagerInterface;
use UserBundle\Domain\Model\User;
use UserBundle\Domain\Repository\UserRepository;
use UserBundle\Infrastructure\Persistence\Mysql\Mapper\UserMapper;
use UserBundle\Domain\ValueObject\UserId;
use UserBundle\Domain\ValueObject\UserEmail;
use UserBundle\Domain\ValueObject\UserName;
use UserBundle\Domain\ValueObject\UserPassword;

class DoctrineUserRepository implements UserRepository
{
    public function __construct(
        private EntityManagerInterface $em,
        private readonly UserMapper $mapper,
    ) {}


    public function findByEmail(UserEmail $email, int $exceptId = null): ?User
    {
        if (!$email->value()) {
            return null;
        }

        $repo = $this->em->getRepository(UserDoctrineEntity::class);
        $query = $repo->createQueryBuilder('u')
            ->andWhere('u.userEmail = :email')
            ->setParameter('email', $email->value());

        if ($exceptId) {
            $query->andWhere('u.id != :exceptId')
                ->setParameter('exceptId', $exceptId);
        }

        $entity = $query->getQuery()->getOneOrNullResult();

        if (!$entity) {
            return null;
        }

        return $this->mapper->toDomain($entity);
    }

    public function findByGoogleId(string $googleId = null): ?User
    {
        if (!$googleId) {
            return null;
        }

        $repo = $this->em->getRepository(UserDoctrineEntity::class);
        $query = $repo->createQueryBuilder('u')
            ->andWhere('u.googleId = :googleId')
            ->setParameter('googleId', $googleId);

        $entity = $query->getQuery()->getOneOrNullResult();

        if (!$entity) {
            return null;
        }

        return $this->mapper->toDomain($entity);
    }

    public function find(?UserId $id): ?User
    {
        $entity = $this->em->find(UserDoctrineEntity::class, $id->value());

        if (!$entity) {
            return null;
        }

        return $this->mapper->toDomain($entity);
    }

    public function save(User $user): int
    {
        $existingEntity = $this->em->getRepository(UserDoctrineEntity::class)
            ->findOneBy(['userEmail' => $user->getUserEmail()->value()]);

        $entity = $this->mapper->toDoctrine($user, $existingEntity);

        if (!$existingEntity) {
            $this->em->persist($entity);
        }

        $this->em->flush();

        return $entity->getId();
    }

    public function delete(UserId $id): bool
    {
        $entity = $this->em->find(UserDoctrineEntity::class, $id->value());

        if ($entity !== null) {
            $this->em->remove($entity);
            $this->em->flush();

            return true;
        }

        return false;
    }
}
