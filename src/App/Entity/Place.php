<?php
namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="place")
 */
class Place implements \Serializable {
    /**
     * @ORM\Id 
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /** 
     * @ORM\Column(type="string", length=128) 
     */
    protected $name;
    
    /** 
     * @ORM\Column(type="string", length=128) 
     */
    protected $adress;
    
    /** 
     * @ORM\Column(type="decimal", precision=10, scale=6) 
     */
    protected $latitude;
    
    /** 
     * @ORM\Column(type="decimal", precision=10, scale=6) 
     */
    protected $longitude;

    /** 
     * @ORM\Column(type="string", length=64) 
     */
    protected $constructor;

    /** 
     * @ORM\ManyToMany(targetEntity="Rappel",mappedBy="places") 
     */
    protected $rappels;

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
     * Add rappel
     * @param Rappel $rappel
     * @return Place
     */
    public function addRappel(Rappel $rappel)
    {
        $this->rappels[] = $rappel;

        return $this;
    }

    /**
     * Remove rappel
     * @param Rappel $rappel
     */
    public function removeRappel(Rappel $rappel)
    {
        $this->rappels->removeElement($rappel);
    }

    /**
     * Get rappels
     * @return ArrayCollection $rappel
     */
    public function getRappel()
    {
        return $this->rappels;
    }

    /**
     * @return String $name($constructor)
     */
    public function __toString()
    {
        return $this->name.' ('.$this->lattitude.','.$this->longitude.')';
    }

}
