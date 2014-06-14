<?php
namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;

/**
 * Class ContactController
 * @package App\Controller
 */
class ContactController implements ControllerProviderInterface
{
    
    /**
     * @param \Silex\Application $app
     * @return mixed
     */
    public function index(Application $app)
    {
        /* Traitement */
        
        return $app['twig']->render(
                'contact/contacts.twig'
        );
    }
    
    /**
     * @param \Silex\Application $app
     * @return mixed
     */
    public function connect(Application $app)
    {
        $index = $app['controllers_factory'];
        $index->match("/", array($this, "index"))->bind('contact.index');
        return $index;
    }
    
}
