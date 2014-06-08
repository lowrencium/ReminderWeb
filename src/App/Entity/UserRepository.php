<?php
namespace App\Entity;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Doctrine\ORM\NoResultException;

/**
 * Class UserRepository
 * @package App\Entity
 */
class UserRepository extends EntityRepository implements UserProviderInterface
{

    /**
     * Loads the user for the given username.
     * @param string $username
     * @return mixed
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function loadUserByUsername($username)
    {
        $q = $this
          ->createQueryBuilder('u')
          ->where('u.username = :username')
          ->setParameter('username', $username)
          ->getQuery()
        ;
        try {
            $user = $q->getSingleResult();
        } catch (NoResultException $e) {
            throw new UsernameNotFoundException(sprintf('Impossible de trouver un utilisateur avec l\'identifiant "%s".', $username));
        }
        return $user;
    }

    /**
     * Loads the user for the given username.
     * @param UserInterface $user
     * @return bool
     */
    public function equals(UserInterface $user)
    {
        return $this->username === $user->getUsername();
    }

    /**
     * Refreshes the user for the account interface.
     * @param UserInterface $user
     * @return null|object
     * @throws \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $class));
        }
        return $this->find($user->getId());
    }

    /**
     * Whether this provider supports the given user class
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return $this->getEntityName() === $class || is_subclass_of($class, $this->getEntityName());
    }
}
