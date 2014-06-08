<?php
namespace App\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_rappel")
 */
class UserRappel
{
    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="rappels")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="App\Entity\Rappel", inversedBy="users")
     * @ORM\JoinColumn(name="rappel_id", referencedColumnName="id", nullable=false)
     */
    protected $rappel;

    /**
     * @ORM\Column(type="date")
     */
    protected $beginShare;

    /**
     * @ORM\Column(type="date")
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
