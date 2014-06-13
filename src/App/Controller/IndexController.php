<?php
namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\Validator\Constraints as Assert;

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
        // Building the login form
        $formLogin = $app['form.factory']->createNamedBuilder('login', 'form')
          ->add('username', 'text')
          ->add('password', 'password')
          ->getForm();
        
        // Building the register form
        $formRegister = $app['form.factory']->createBuilder('form')
            ->add('email', 'text',
              array(
                'label' => 'E-mail',
                'constraints' => new Assert\Regex("/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/"),
                'required' => true
              )
            )
            ->add('password', 'password', array(
                'mapped' => false,
                //'type' => 'password',
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'required' => true,
                //'first_options'  => array('label_attr' => array('style' => 'display:none;'), 'attr' => array('placeholder' => 'Mot de passe')),
            ))
            ->add('role','choice', array(
                'label' => 'Compte',
                'choices' => array( 1 => 'Classique', 2 => 'Organisation/Entreprises'),
                'expanded' => true,
            ))
            ->getForm();
        
        return $app['twig']->render(
                'html.twig',
                array(
                    'formlogin' => $formLogin->createView(),
                    'formregister' => $formRegister->createView(),
                )
        );
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
