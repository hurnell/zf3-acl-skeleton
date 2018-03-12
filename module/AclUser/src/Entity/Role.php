<?php

/**
 * Class Role
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
 * Role
 *
 * @ORM\Table(name="role", uniqueConstraints={@ORM\UniqueConstraint(name="name_idx", columns={"name"})}, indexes={@ORM\Index(name="parent_id_idx", columns={"parent_id"})})
 * @ORM\Entity
 * 
 * @package     AclUser\Entity
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class Role
{

    /**
     * 
     * @var integer The database id of this role.
     *
     * @ORM\Column(name="id", type="integer", precision=0, scale=0, nullable=false, unique=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * 
     * @var string The readable name of the role
     *
     * @ORM\Column(name="name", type="string", length=128, precision=0, scale=0, nullable=false, unique=false)
     */
    private $name;

    /**
     * 
     * @var string Short description that explains the overall usage of the role.
     *
     * @ORM\Column(name="description", type="text", length=65535, precision=0, scale=0, nullable=true, unique=false)
     */
    private $description;

    /**
     * @var boolean whether the role is active 
     *
     * @ORM\Column(name="active", type="boolean", nullable=true, options={ "default":true})
     */
    private $active;

    /**
     * @var \AclUser\Entity\Role The single parent (Role entity) from which this role inherits
     *
     * @ORM\ManyToOne(targetEntity="AclUser\Entity\Role")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $parent;

    /**
     * Set id (used for unit testing)
     * 
     * @param integer $id the id of the dummy user
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

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
     * Set name
     *
     * @param string $name
     *
     * @return Role
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Role
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Role
     */
    /*
      public function setActive($active)
      {
      $this->active = $active;

      return $this;
      } */

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set parent
     *
     * @param \AclUser\Entity\Role $parent
     *
     * @return Role
     */
    public function setParent(\AclUser\Entity\Role $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \AclUser\Entity\Role
     */
    public function getParent()
    {
        return $this->parent;
    }

}
