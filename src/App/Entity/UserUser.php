<?php
namespace App\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_user")
 */
class UserUser
{
    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="users")
     * @ORM\JoinColumn(name="user1_id", referencedColumnName="id", nullable=false)
     */
    protected $user1;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="users")
     * @ORM\JoinColumn(name="user2_id", referencedColumnName="id", nullable=false)
     */
    protected $user2;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $validated;


    /**
     * @param User $user
     */
    public function setUser1(User $user)
    {
        $this->user1 = $user;
    }
    
    /**
     * @param User $user
     */
    public function setUser2(User $user)
    {
        $this->user2 = $user;
    }

    /**
     * @return User
     */
    public function getUser1()
    {
        return $this->user1;
    }

    /**
     * @return User
     */
    public function getUser2()
    {
        return $this->user2;
    }
}
