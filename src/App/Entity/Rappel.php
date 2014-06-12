<?php
namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="rappel")
 */
class Rappel implements \Serializable {
    /**
     * @ORM\Id 
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /** 
     * @ORM\Column(type="string", length=255) 
     */
    private $description;
    
    /** 
     * @ORM\Column(type="string", length=255) 
     */
    private $cycle;
    
    /**
     * @ORM\Column(type="date")
     */
    private $begin;

    /**
     * @ORM\Column(type="date")
     */
    private $end;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;
    
    /** 
     * @ORM\Column(type="string", length=255) 
     */
    private $lieu;
    
    /**
     * @ORM\Column(type="date")
     */
    private $lastUpdate;

    /** 
     * @ORM\ManyToMany(targetEntity="User",mappedBy="rappels") 
     */
    private $users;
    
    /**
     * @ORM\ManyToMany(targetEntity="Place",cascade="persist",inversedBy="rappels")
     * @ORM\JoinTable(name="rappels_places")
     */
    protected $places;

    /**
     * Constructor
     */
    public function __construct(){
        $this->users = new ArrayCollection();
        $this->places = new ArrayCollection();
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
     * Set description
     * @param string $description
     * @return Rappel
     */
    public function setDescription($description)
    {
        $this->serial = $description;

        return $this;
    }

    /**
     * Get description
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set cycle
     * @param string $cycle
     * @return Rappel
     */
    public function setCycle($cycle)
    {
        $this->cycle = $cycle;

        return $this;
    }

    /**
     * Get cycle
     * @return string 
     */
    public function getCycle()
    {
        return $this->cycle;
    }
    
    /**
     * Set lieu
     * @param string $lieu
     * @return Lieu
     */
    public function setLieu($lieu)
    {
        $this->lieu = $lieu;

        return $this;
    }

    /**
     * Get lieu
     * @return string 
     */
    public function getLieu()
    {
        return $this->lieu;
    }

    /**
     * Add user
     * @param User $user
     * @return Rappel
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
