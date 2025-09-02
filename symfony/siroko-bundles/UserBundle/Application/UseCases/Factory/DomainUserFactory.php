<?php

namespace UserBundle\Application\UseCases\Factory;

use UserBundle\Application\UseCases\Contracts\UserUpsertData;
use UserBundle\Domain\Model\User as DomainUser;
use UserBundle\Domain\ValueObject\UserEmail;
use UserBundle\Domain\ValueObject\UserName;
use UserBundle\Domain\ValueObject\UserPassword;
use UserBundle\Domain\Exception\ValidationException;

final class DomainUserFactory
{
    public static function fromCommand(
        UserUpsertData $command,
        ?DomainUser $currentDomainUser,
        ?string $hashed = null,
        ?array $roles = [],
    ): DomainUser
    {
        $passwordHash = $hashed ?? $command->getPassword();
        if ($currentDomainUser === null) {
            if ($passwordHash === null) {
                throw new ValidationException(['Hashed password es obligatorio al registrar.'], 500);
            }
            return new DomainUser(
                new UserName($command->getName()),
                new UserEmail($command->getEmail()),
                new UserPassword($passwordHash),
            );
        }

        $currentDomainUser->setUserName(new UserName($command->getName()));
        $currentDomainUser->setUserEmail(new UserEmail($command->getEmail()));

        $userEnabled = $command->isEnabled() ?? $currentDomainUser->isEnabled();
        $currentDomainUser->setEnabled($userEnabled);

        if (!empty($passwordHash) && $passwordHash !== $currentDomainUser->getUserPassword()->value()) {
            $currentDomainUser->setUserPassword(new UserPassword($passwordHash));
        }

        if(!empty($command->roles) && is_array($command->roles))
        {
            $currentDomainUser->setRoles($command->roles);
        }

        return $currentDomainUser;
    }
}
