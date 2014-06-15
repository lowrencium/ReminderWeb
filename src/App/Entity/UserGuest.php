<?php
namespace App\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_guest")
 */
class UserGuest
{
    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="guests")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="App\Entity\Guest", inversedBy="users")
     * @ORM\JoinColumn(name="guest_id", referencedColumnName="id", nullable=false)
     */
    protected $guest;


    /**
     * @param Guest $guest
     */
    public function setGuest(Guest $guest)
    {
        $this->guest = $guest;
    }

    /**
     * @return Guest
     */
    public function getGuest()
    {
        return $this->guest;
    }

    /**
     * @param $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }
}
