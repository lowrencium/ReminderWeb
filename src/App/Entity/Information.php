<?php
namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\@Table(name="information")
 */
class Information implements \Serializable {
    /**
     * @ORM\@Id 
     * @ORM\@Column(type="integer")
     * @ORM\@GeneratedValue
     */
    private $id;

    /** 
     * @ORM\@Column(type="string", length=255) 
     */
    private $description;
    
    /** 
     * @ORM\@Column(type="string", length=255) 
     */
    private $content;

    /** 
     * @ORM\@ManyToMany(targetEntity="User",mappedBy="informations") 
     */
    private $users;

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
     * Set description
     * @param string $description
     * @return Information
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
     * Set content
     * @param string $content
     * @return Information
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Add user
     * @param User $user
     * @return Information
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
