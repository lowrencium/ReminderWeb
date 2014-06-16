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
    private $formLogin;
    private $formRegister;
    
    /**
     * @param \Silex\Application $app
     * @return mixed
     */
    public function index(Application $app)
    {
        $this->generateForms($app);
        $user = $app['security']->getToken()->getUser();
        $config = $app['configuration']->load(__DIR__ . "/../../../config/config.yml");
        
        return $app['twig']->render(
                'home.twig',
                array(
                    'formlogin' => $this->formLogin->createView(),
                    'formregister' => $this->formRegister->createView(),
                    'user' => $user,
                    'webservice' => $config->get('webservice',array())
                )
        );
    }

    /**
     * @param \Silex\Application $app
     * @return mixed
     */
    public function contact(Application $app)
    {
        $this->generateForms($app);
        
        return $app['twig']->render(
                'contactUs.twig',
                array(
                    'formlogin' => $this->formLogin->createView(),
                    'formregister' => $this->formRegister->createView(),
                )
        );
    }
    
    /**
     * @param \Silex\Application $app
     * @return mixed
     */
    public function about(Application $app)
    {
        $this->generateForms($app);
        
        return $app['twig']->render(
                'aboutUs.twig',
                array(
                    'formlogin' => $this->formLogin->createView(),
                    'formregister' => $this->formRegister->createView(),
                )
        );
    }
    
    /**
     * @param \Silex\Application $app
     */
    private function generateForms(Application $app){
        // Building the login form
        $this->formLogin = $app['form.factory']->createNamedBuilder('login', 'form')
          ->add('username', 'text')
          ->add('password', 'password')
          ->getForm();
        
        // Building the register form
        $this->formRegister = $app['form.factory']->createBuilder('form')
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
    }
    
    /**
     * @param \Silex\Application $app
     * @return mixed
     */
    public function connect(Application $app)
    {
        $index = $app['controllers_factory'];
        $index->match("/", array($this, "index"))->bind('index');
        $index->match("/contactus", array($this, "contact"))->bind('index.contact');
        $index->match("/aboutus", array($this, "about"))->bind('index.about');
        return $index;
    }
    
}
