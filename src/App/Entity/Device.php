<?php
namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="device")
 */
class Device implements \Serializable {
    /**
     * @ORM\Id 
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /** 
     * @ORM\Column(type="string", length=128, unique=true) 
     */
    protected $serial;

    /** 
     * @ORM\Column(type="string", length=64) 
     */
    protected $name;

    /** 
     * @ORM\Column(type="string", length=64) 
     */
    protected $constructor;

    /** 
     * @ORM\ManyToMany(targetEntity="User",mappedBy="devices") 
     */
    protected $users;

    /**
     * Constructor
     */
    public function __construct(){
        $this->users = new ArrayCollection();
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
        ));
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        list (
          $this->id,
          ) = unserialize($serialized);
    }

    /**
     * Get id
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set serial
     * @param string $serial
     * @return Device
     */
    public function setSerial($serial)
    {
        $this->serial = $serial;

        return $this;
    }

    /**
     * Get serial
     * @return string 
     */
    public function getSerial()
    {
        return $this->serial;
    }

    /**
     * Set name
     * @param string $name
     * @return Device
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set constructor
     * @param string $constructor
     * @return Device
     */
    public function setConstructor($constructor)
    {
        $this->constructor = $constructor;

        return $this;
    }

    /**
     * Get constructor
     * @return string 
     */
    public function getConstructor()
    {
        return $this->constructor;
    }

    /**
     * Add user
     * @param User $user
     * @return Device
     */
    public function addUser(User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     * @param User $user
     */
    public function removeUser(User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     * @return ArrayCollection $users
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @return String $name($constructor)
     */
    public function __toString()
    {
        return $this->name.' ('.$this->constructor.')';
    }

}
