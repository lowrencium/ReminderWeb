<?php
namespace App\Entity;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="UserRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="user")
 */
class User implements AdvancedUserInterface, \Serializable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $salt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     */
    private $firstname;
    
    /**
     * @ORM\Column(type="string", length=60, unique=true)
     */
    private $lastname;
    
    /**
     * @ORM\Column(type="string", length=60, unique=true)
     */
    private $phone;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $roles;

    /**
     * @ORM\Column(type="integer", name="level")
     */
    private $experience;

    /**
     * @ORM\Column(type="boolean", name="active")
     */
    private $active;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime", name="last_login", nullable=true)
     */
    protected $lastLogin;

    /**
     * @ORM\Column(type="integer", name="nb_login")
     */
    protected $nbLogin = 0;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserLogin", mappedBy="user")
     * @ORM\OrderBy({"created" = "DESC"})
     */
    protected $logins;

    /**
     * @ORM\ManyToMany(targetEntity="Device",cascade="persist",inversedBy="users")
     * @ORM\JoinTable(name="users_devices")
     */
    protected $devices;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Rappel", mappedBy="user")
     */
    protected $rappels;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Information", mappedBy="user")
     */
    protected $informations;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->active = true;
        $this->salt = md5(uniqid(null, true));
        $this->experience = 0;
        $this->logins = new ArrayCollection();
        $this->devices = new ArrayCollection();
        $this->rappels = new ArrayCollection();
        $this->informations = new ArrayCollection();
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }
    
    /**
     * @param $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return mixed
     */
    public function getFirstname()
    {
        return $this->firstname;
    }
    
    /**
     * @param $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return mixed
     */
    public function getLastname()
    {
        return $this->lastname;
    }
    
    /**
     * @param $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }
    
    /**
     * Returns the password used to authenticate the user.
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $password
     * @return mixed
     */
    public function setPassword($password)
    {
        return $this->password = $password;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     * @return string The salt
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @param array $role
     */
    public function setRole($role)
    {
        $this->roles = $role;
    }
    
    /**
     * @param array $roles
     */
    public function setRoles($roles)
    {
        $this->roles = implode(",", $roles);
    }

    /**
     * Returns the roles granted to the user.
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        $roles = explode(",", $this->roles);
        array_push($roles, 'ROLE_USER');
        return array_unique(array_filter($roles));
    }

    /**
     * @param $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $lastLogin
     */
    public function setLastLogin($lastLogin)
    {
        $this->lastLogin = $lastLogin;
    }

    /**
     * @return mixed
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * @param mixed $nbLogin
     */
    public function setNbLogin($nbLogin)
    {
        $this->nbLogin = $nbLogin;
    }

    /**
     * @return mixed
     */
    public function getNbLogin()
    {
        return $this->nbLogin;
    }

    /**
     * @param $logins
     */
    public function setLogins($logins)
    {
        $this->logins = $logins;
    }

    /**
     * @return mixed
     */
    public function getLogins()
    {
        return $this->logins;
    }

    /**
     * Add login
     *
     * @param $login
     */
    public function addLogin($login)
    {
        if(!$this->logins->contains($login)){
            $this->logins[]= $login;
        }
    }

    /**
     * Remove login
     *
     * @param $login
     */
    public function removeLogin($login){
        $this->logins->removeElement($login);
    }
    
    /**
     * Add devices
     *
     * @param \App\Entity\Device $devices
     * @return User
     */
    public function addDevice(\App\Entity\Device $devices)
    {
        if(!$this->devices->contains($devices)){
            $this->devices[] = $devices;
        }

        return $this;
    }
    
    /**
     * Remove devices
     *
     * @param \App\Entity\Device $devices
     */
    public function removeDevice(\App\Entity\Device $devices)
    {
        $this->devices->removeElement($devices);
    }

    /**
     * Get devices
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDevices()
    {
        return $this->devices;
    }
    
    /**
     * During the entity creation, set the creation date
     *
     * @ORM\PrePersist()
     */
    public function onPrePersist()
    {
        $this->setCreated(new \DateTime("now"));
    }

    /**
     * Removes sensitive data from the user.
     * @return void
     */
    public function eraseCredentials()
    {

    }

    /**
     * @return bool
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->active;
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
        ));
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list (
          $this->id,
          ) = unserialize($serialized);
    }

    /**
     * @param ClassMetadata $metadata
     */
    static public function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(new UniqueEntity(array(
            'fields' => array('email'),
            'message' => "Cette adresse mail est déjà associée à un autre utilisateur."
        )));
    }
}
