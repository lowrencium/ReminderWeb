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
        // Building the form
        $form = $app['form.factory']->createNamedBuilder('login', 'form')
          ->add('username', 'text')
          ->add('password', 'password')
          ->getForm();
        
        return $app['twig']->render('html.twig',array('form' => $form->createView()));
    }

    /**
     * @param \Silex\Application $app
     * @return mixed
     */
    public function connect(Application $app)
    {
        $index = $app['controllers_factory'];
        $index->match("/", array($this, "index"))->bind('index');
        $index->match("/calendar", array($this, "calendar"))->bind('calendar');
        return $index;
    }
    
     public function calendar(Application $app)
    {
        return $app['twig']->render('calendar.twig');
    }
}
