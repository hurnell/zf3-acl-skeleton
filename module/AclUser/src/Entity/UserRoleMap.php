<?php

/**
 * Class UserRoleMap
 *
 * @package     AclUser\Entity
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserRoleMap
 * 
 * @package     AclUser\Entity
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 *
 * @ORM\Table(name="user_role_map", indexes={
 * @ORM\Index(name="user_id_idx", columns={"user_id"}), 
 * @ORM\Index(name="role_id_idx", columns={"role_id"}
 * )})
 * @ORM\Entity
 */
class UserRoleMap
{

    /**
     * @var integer The database id for this user role map entry
     *
     * @ORM\Column(name="id", type="integer", precision=0, scale=0, nullable=false, unique=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AclUser\Entity\Role The role entity that corresponds to this user role map entry.
     *
     * @ORM\ManyToOne(targetEntity="AclUser\Entity\Role")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="role_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $role;

    /**
     * @var \AclUser\Entity\User The user entity that corresponds to this user role map entity
     *
     * @ORM\ManyToOne(targetEntity="AclUser\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $user;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set role
     *
     * @param \AclUser\Entity\Role $role
     *
     * @return UserRoleMap
     */
    public function setRole(\AclUser\Entity\Role $role = null)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return \AclUser\Entity\Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set user
     *
     * @param \AclUser\Entity\User $user
     *
     * @return UserRoleMap
     */
    public function setUser(\AclUser\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AclUser\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

}
