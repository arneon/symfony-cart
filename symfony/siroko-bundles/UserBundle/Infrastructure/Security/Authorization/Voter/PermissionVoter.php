<?php

namespace UserBundle\Infrastructure\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Vota sobre atributos tipo LET_* (permisos).
 * Traduce roles alcanzables (via RoleHierarchy) -> permisos (LET_*), con un map inyectado.
 */
final class PermissionVoter extends Voter
{
    public function __construct(
        private readonly RoleHierarchyInterface $roleHierarchy,
        /** @var array<string, string[]> map: ROLE_* => [LET_* ...] */
        private readonly array $permissionMap = [],
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        // Solo nos interesan los permisos del estilo LET_*
        return str_starts_with($attribute, 'LET_');
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        // 1) roles directos del token
        $userRoleNames = $token->getRoleNames();

        // 2) expande la jerarquía (ROLE_ADMIN -> ROLE_USER_MANAGE -> ...)
        $reachableRoles = $this->roleHierarchy->getReachableRoleNames($userRoleNames);

        // 3) permisos “implícitos” por rol según el mapa
        $grantedPermissions = [];
        foreach ($reachableRoles as $roleName) {
            if (!empty($this->permissionMap[$roleName])) {
                foreach ($this->permissionMap[$roleName] as $perm) {
                    if (str_starts_with($perm, 'LET_')) {
                        $grantedPermissions[$perm] = true;
                    }
                }
            }
        }

        // 4) también acepta permisos asignados directamente al usuario (si los hubiera)
        foreach ($userRoleNames as $maybePermission) {
            if (str_starts_with($maybePermission, 'LET_')) {
                $grantedPermissions[$maybePermission] = true;
            }
        }

        return isset($grantedPermissions[$attribute]);
    }
}
