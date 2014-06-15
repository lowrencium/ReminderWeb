<?php
namespace App\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="guest_rappel")
 */
class GuestRappel
{
    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="App\Entity\Guest", inversedBy="rappels")
     * @ORM\JoinColumn(name="guest_id", referencedColumnName="id", nullable=false)
     */
    protected $guest;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="App\Entity\Rappel", inversedBy="guests")
     * @ORM\JoinColumn(name="rappel_id", referencedColumnName="id", nullable=false)
     */
    protected $rappel;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $beginShare;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $endShare;


    /**
     * @param Rappel $rappel
     */
    public function setRappel(Rappel $rappel)
    {
        $this->rappel = $rappel;
    }

    /**
     * @return Rappel
     */
    public function getRappel()
    {
        return $this->rappel;
    }

    /**
     * @param $guest
     */
    public function setGuest($guest)
    {
        $this->guest = $guest;
    }

    /**
     * @return mixed
     */
    public function getGuest()
    {
        return $this->guest;
    }
}
