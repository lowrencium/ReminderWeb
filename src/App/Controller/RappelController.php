<?php
namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;

/**
 * Class RappelController
 * @package App\Controller
 */
class RappelController implements ControllerProviderInterface
{
    
    /**
     * @param \Silex\Application $app
     * @return mixed
     */
    public function index(Application $app)
    {
        /* Traitement */
        
        return $app['twig']->render(
                'rappel/calendar.twig'
        );
    }
    
    /**
     * @param \Silex\Application $app
     * @return mixed
     */
    public function connect(Application $app)
    {
        $index = $app['controllers_factory'];
        $index->match("/", array($this, "index"))->bind('rappel.index');
        return $index;
    }
    
}
