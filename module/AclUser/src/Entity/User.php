<?php

/**
 * Class User
 *
 * @package     AclUser\Entity
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * User
 *
 * @ORM\Table(name="user", uniqueConstraints={@ORM\UniqueConstraint(name="email_idx", columns={"email"})})
 * @ORM\Entity
 * 
 * @package     AclUser\Entity
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class User
{

    // User status constants.
    const STATUS_ACTIVE = true; // Active user.
    const STATUS_RETIRED = false; // Retired user.

    /**
     * @var integer The database id of this user
     *
     * @ORM\Column(name="id", type="integer", precision=0, scale=0, nullable=false, unique=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string The e-mail address of this user.
     *
     * @ORM\Column(name="email", type="string", length=128, precision=0, scale=0, nullable=false, unique=false)
     */
    private $email;

    /**
     * @var string the full name of this user (if given)
     *
     * @ORM\Column(name="full_name", type="string", length=255, precision=0, scale=0, nullable=false, unique=false)
     */
    private $fullName;

    /**
     * @var string The (hashed) password for this user.
     *
     * @ORM\Column(name="password", type="string", length=255, precision=0, scale=0, nullable=false, unique=false)
     */
    private $password;

    /**
     * @var boolean Whether user has upload a photo or not.
     *
     * @ORM\Column(name="photo", type="boolean", nullable=true, options={ "default":false})
     */
    private $photo;

    /**
     * @var boolean The status of this user ie. active = true or retired = false.
     *
     * @ORM\Column(name="status", type="boolean", nullable=true, options={ "default":false})
     */
    private $status;

    /**
     * @var \DateTime The date time that this user account was created.
     *
     * @ORM\Column(name="date_created", type="datetime", precision=0, scale=0, nullable=false, unique=false)
     */
    private $dateCreated;

    /**
     * @var string A token that is created when a user forgets their password
     * It's sent to them via e-mail and checked on the return request.
     *
     * @ORM\Column(name="pwd_reset_token", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $pwdResetToken;

    /**
     * @var \DateTime The date time when the password reset token is created 
     * So that the reset request has only a fixed time limit.
     *
     * @ORM\Column(name="pwd_reset_token_date", type="datetime", precision=0, scale=0, nullable=true, unique=false)
     */
    private $pwdResetTokenDate;

    /**
     * @var ArrayCollection a collection of UserRoleMap entities associated with this User entity.
     * 
     * @ORM\OneToMany(targetEntity="UserRoleMap", mappedBy="user", cascade={"persist"})
     */
    protected $roleMaps;

    /**
     * Entity constructor that initialises the role map collection
     */
    public function __construct()
    {
        $this->roleMaps = new ArrayCollection();
    }

    /**
     * Add Array Collection of UserRoleMaps (Unit tests)
     * 
     * @param ArrayCollection $roleMaps
     * @return $this
     */
    public function setRoleMaps(ArrayCollection $roleMaps)
    {
        $this->roleMaps = $roleMaps;
        return $this;
    }

    /**
     * Get a collection of user role maps for this user.
     * 
     * @return ArrayCollection 
     */
    public function getRoleMaps()
    {
        return $this->roleMaps;
    }

    /**
     * Set the id for this user used in unit tests only
     * 
     * @param integer $id
     * @return User
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
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set fullName
     *
     * @param string $fullName
     *
     * @return User
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * Get fullName
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set photo whether user has uploaded a photo
     * 
     * @param boolean $photo 
     * @return User user entity object
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
        return $this;
    }

    /**
     * Get whether user has uploaded a photo
     * 
     * @return boolean
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set status
     *
     * @param boolean $status
     *
     * @return User
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * retire user if active or make active if retired
     */
    public function toggleStatus()
    {
        if ($this->getStatus() == self::STATUS_RETIRED) {
            $this->setStatus(self::STATUS_ACTIVE);
        } else {
            $this->setStatus(self::STATUS_RETIRED);
        }
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     *
     * @return User
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * Get dateCreated
     *
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * Set pwdResetToken
     *
     * @param string $pwdResetToken
     *
     * @return User
     */
    public function setPwdResetToken($pwdResetToken)
    {
        $this->pwdResetToken = $pwdResetToken;

        return $this;
    }

    /**
     * Get pwdResetToken
     *
     * @return string
     */
    public function getPwdResetToken()
    {
        return $this->pwdResetToken;
    }

    /**
     * Set pwdResetTokenDate
     *
     * @param \DateTime $pwdResetTokenDate
     *
     * @return User
     */
    public function setPwdResetTokenDate($pwdResetTokenDate)
    {
        $this->pwdResetTokenDate = $pwdResetTokenDate;

        return $this;
    }

    /**
     * Get pwdResetTokenDate
     *
     * @return \DateTime
     */
    public function getPwdResetTokenDate()
    {
        return $this->pwdResetTokenDate;
    }

}
