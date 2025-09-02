<?php

namespace UserBundle\Infrastructure\Persistence\Mysql\Mapper;

use UserBundle\Domain\Model\User as DomainUser;
use UserBundle\Domain\ValueObject\UserEmail;
use UserBundle\Domain\ValueObject\UserId;
use UserBundle\Domain\ValueObject\UserName;
use UserBundle\Domain\ValueObject\UserPassword;
use UserBundle\Infrastructure\Persistence\Mysql\UserDoctrineEntity as EntityUser;
use UserBundle\Application\UseCases\RegisterUser\RegisterUserCommand as Command;
use UserBundle\Application\UseCases\UpdateUser\UpdateUserCommand;

final class UserMapper
{
    public static function toDoctrine(DomainUser $domain, ?EntityUser $entity = null): EntityUser
    {
        $entity ??= new EntityUser();
        $entity->setUserName($domain->getUserName()->value());
        $entity->setUserEmail($domain->getUserEmail()->value());
        $entity->setPassword($domain->getUserPassword()->value());
        $entity->setCreatedAt($domain->getCreatedAt());
        $entity->setUpdatedAt($domain->getUpdatedAt());
        $entity->setEnabled($domain->isEnabled());

        $entity->setRoles($domain->getRoles());

        return $entity;
    }

    public static function toDomain(EntityUser $entity): DomainUser
    {
        $domainUser = new DomainUser(
            new UserName($entity->getUserName()),
            new UserEmail($entity->getUserEmail()),
            new UserPassword($entity->getPassword()),
        );
        $id = $entity->getId();

        if ($id !== null) {
            self::setPrivate($domainUser, 'userId', new UserId((int)$id));
        }

        $domainUser->setRoles($entity->getRoles());
        $domainUser->setEnabled($entity->isEnabled());

        return $domainUser;
    }

    public static function toArray(DomainUser $domain): array
    {
        return [
            'id'    => $domain->getUserId(),
            'name'  => $domain->getUserName()->value(),
            'email' => $domain->getUserEmail()->value(),
            'enabled' => $domain->isEnabled(),
            'roles' => $domain->getRoles(),
        ];
    }

    public static function CommandToDomain(Command $command, string $hashed = null): DomainUser
    {
        $user = new DomainUser(
            new UserName($command->name),
            new UserEmail($command->email),
            new UserPassword($hashed),
        );

        return $user;
    }


    private static function setPrivate(object $object, string $property, mixed $value): void
    {
        $ref = new \ReflectionObject($object);
        if (!$ref->hasProperty($property)) {
            return;
        }
        $prop = $ref->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($object, $value);
    }
}
