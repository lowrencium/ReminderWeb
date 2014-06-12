<?php
namespace App\Doctrine\Persistence;

use Doctrine\Common\Persistence\AbstractManagerRegistry;
use Silex\Application;

/**
 * References Doctrine connections and entity/document managers.
 *
 * @author Саша Стаменковић <umpirsky@gmail.com>
 */
class ManagerRegistry extends AbstractManagerRegistry
{
    protected $container;

    protected function getService($name)
    {
        return $this->container[$name];
    }

    protected function resetService($name)
    {
        unset($this->container[$name]);
    }

    public function getAliasNamespace($alias)
    {
        throw new \BadMethodCallException('Namespace aliases not supported.');
    }

    public function setContainer(Application $container)
    {
        $this->container = $container;
    }
}
