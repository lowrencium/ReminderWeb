<?php
namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;

/**
 * Class IndexController
 * @package App\Controller
 */
class IndexController implements ControllerProviderInterface
{
    /**
     * @param \Silex\Application $app
     * @return mixed
     */
    public function index(Application $app)
    {
        $vars = array('hello' => 'world');
        return $app['twig']->render('html.twig', $vars);
    }

    /**
     * @param \Silex\Application $app
     * @return mixed
     */
    public function connect(Application $app)
    {
        $index = $app['controllers_factory'];
        $index->match("/", array($this, "index"))->bind('index');
        return $index;
    }
}
