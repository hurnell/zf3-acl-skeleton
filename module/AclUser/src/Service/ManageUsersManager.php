<?php

/**
 * Class ManageUsersManager
 *
 * @package     AclUser\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Service;

use AclUser\Entity\User;
use AclUser\Entity\Role;
use AclUser\Entity\UserRoleMap;

/**
 * This service is responsible for the logic of adding/editing users
 * and changing user passwords by admin staff.
 * 
 * @package     AclUser\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class ManageUsersManager extends UserManager
{

    /**
     * Get ArrayCollection of system users
     * 
     * @return ArrayCollection
     */
    public function getAllUsers()
    {
        return $this->entityManager->getRepository(User::class)
                        ->findBy([], ['id' => 'ASC']);
    }

    /**
     * Get User by their database id
     * 
     * @param integer $id
     * @return User|null
     */
    public function findUserById($id)
    {
        return $this->entityManager->getRepository(User::class)
                        ->findOneBy(['id' => $id]);
    }

    /**
     * Get Role by its database id
     * 
     * @param integer $id
     * @return Role|null
     */
    public function findRoleById($id)
    {
        return $this->entityManager->getRepository(Role::class)
                        ->findOneBy(['id' => $id]);
    }

    /**
     * Get this users roles and all possible roles
     * 
     * @param User $user
     * @return array of ArrayCaollections This user's Roles & All possible Roles
     */
    public function getRolesByUser($user)
    {
        $userRoles = isset($user) ? $user->getRoleMaps() : null;
        $allRoles = $this->entityManager->getRepository(Role::class)
                ->findBy(['active' => true]);
        foreach ($userRoles as $userRole) {
            if (in_array($userRole->getRole(), $allRoles)) {
                unset($allRoles[array_search($userRole->getRole(), $allRoles)]);
            }
            if (!$userRole->getRole()->getActive()) {
                $userRoles->removeElement($userRole);
            }
        }
        return [$userRoles, $allRoles];
    }

    /**
     * Grant or revoke User specific Role
     * 
     * @param string $type add or remove action
     * @param int $userId the user's id
     * @param int $roleId the role's id
     */
    public function updateUserRoleMembership($type, $userId, $roleId)
    {

        if ('add' === $type) {
            $entity = new UserRoleMap();
            $entity->setUser($this->findUserById($userId));
            $entity->setRole($this->findRoleById($roleId));
            $this->entityManager->persist($entity);
        } else if ('remove' === $type) {
            $entity = $this->entityManager->getRepository(UserRoleMap::class)
                    ->findOneBy(['user' => $this->findUserById($userId), 'role' => $this->findRoleById($roleId)]);
            $this->entityManager->remove($entity);
        }
        $this->entityManager->flush();
    }

    /**
     * Toggle suspended status of a user
     * 
     * @param integer $id
     * @return boolean
     */
    public function toggleSuspensionUserById($id)
    {
        $entity = $this->getUserById($id);
        $entity->toggleStatus();
        $this->entityManager->merge($entity);
        $this->entityManager->flush();
        return true;
    }

    /**
     * Delete user from database based on the db id
     * 
     * @param integer $id
     */
    public function deleteUserById($id)
    {
        $entity = $this->getUserById($id);
        /* note that user_role_map entries are deleted by database constraint */
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

}
