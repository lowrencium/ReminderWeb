<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\UserRepository;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;

/**
 * Class UserController
 * @package App\Controller
 */
class UserController implements ControllerProviderInterface
{
    /**
     * @param Application $app
     * @param Request $request
     * @return mixed
     */
    public function login(Application $app, Request $request)
    {
        // Building the form
        $form = $app['form.factory']->createNamedBuilder('login', 'form')
          ->add('username', 'text')
          ->add('password', 'password')
          ->getForm();

        $form_error = $app['security.last_error']($request);
        if ($form_error != null) {
            if(strcmp($form_error,"User account is disabled.") == 0){
                $form->addError(new FormError("Votre compte n'est pas activé."));
            }
            else {
                $form->addError(new FormError("L'identifiant ou le mot de passe ne sont pas valides !"));
            }
        }

        return $app['twig']->render('html.twig', array('form' => $form->createView()));
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return mixed
     */
    public function signin(Application $app, Request $request)
    {
        $response = array("tag" => "login", "success" => 0, "error" => 0);
        if ($request->isMethod('GET'))
        {
            $data = $request->query->all();
            if(!empty($data['email'])){
                $user = $app['orm.em']->getRepository('App\Entity\User')
                        ->findOneByEmail($data['email']);
            }
            if(empty($user)){
                $response["error"] = 1;
                $response["error_msg"] = "Wrong credential";
            }
            else {
                $password = "";
                if(!empty($data['password'])){
                    $password = $app['security.encoder_factory']
                        ->getEncoder($user)
                        ->encodePassword($data['password'],$user->getSalt());
                }

                if($user->getPassword() == $password){
                    $response["success"] = 1;
                    $response["uid"] = "4f074ca1e3df49.06340261";
                    $response["user"]["name"] = $user->getFirstName();
                    $response["user"]["email"] = $user->getEmail();
                    $response["user"]["created_at"] = $user->getCreated()->format('Y-m-d H:i:s');
                    $response["user"]["updated_at"] = $user->getLastLogin();
                }
                else {
                    $response["error"] = 1;
                    $response["error_msg"] = "Wrond credential";
                }
            }
        }
        return new Response(json_encode($response), 200);
    }
    
    /**
     * @param Application $app
     * @param Request $request
     * @return mixed
     */
    public function signup(Application $app, Request $request)
    {
        $em = $app["orm.em"];
        $response = array("tag" => "register", "success" => 0, "error" => 0);
        if ($request->isMethod('GET'))
        {
            $data = $request->query->all();
            if(!empty($data['firstname']) && !empty($data['lastname']) 
                    && !empty($data['email']) && !empty($data['password'])){
                $user = new User();

                // user
                $user = new User();
                $user->setFirstname($data['firstname']);
                $user->setLastname($data['lastname']);
                $user->setPhone('0000000');
                $user->setActive(1);
                $user->setUsername($data['email']);
                $user->setEmail($data['email']);

                // encoding password
                $user->setPassword($app['security.encoder_factory']
                        ->getEncoder($user)
                        ->encodePassword($data['password'],$user->getSalt())
                );

                // Persist
                $em->persist($user);
                $em->flush();

                // return response
                $response["success"] = 1;
                $response["uid"] = "4f074ca1e3df49.06340261";
                $response["user"]["name"] = $user->getFirstName();
                $response["user"]["email"] = $user->getEmail();
                $response["user"]["created_at"] = $user->getCreated()->format('Y-m-d H:i:s');
                $response["user"]["updated_at"] = $user->getLastLogin();
            }
            else {
                $response["error"] = 1;
                $response["error_msg"] = "Bad Usage";
            }
        } 
        else {
            $response["error"] = 1;
            $response["error_msg"] = "Wrong credential";
        }
        return new Response(json_encode($response), 200);
    }
    
    /**
     * @param Application $app
     * @param Request $request
     * @return mixed
     */
    public function register(Application $app, Request $request)
    {        
        // Building the form
        $builder = $app['form.factory']->createBuilder('form')
            ->add('email', 'text',
              array(
                'label' => 'E-mail',
                'constraints' => new Assert\Regex("/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/"),
                'required' => true
              )
            )
            ->add('password', 'repeated', array(
                'mapped' => false,
                'type' => 'password',
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'required' => true,
                'first_options'  => array('label' => 'Mot de passe'),
                'second_options' => array('label' => 'Confirmation')
            ))
            ->add('role','select', array(
                'label' => 'Type de compte'
            ));

        $processed = false;
        $form = $builder->getForm();
        if ($request->isMethod('POST')) {
            $form->get('email')->setData("none");
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $app["orm.em"];
                $processed = 1;
                $timestamp = time();
                
                // Retrieving data
                $email = $form->get('email')->getData();
                $password = $form->get('password')->getData();
                $site = $form->get('site')->getData();
                
                // Verify if the user already exist
                $user = $em->getRepository('App\\Entity\\User')->findOneBy(array('email' => $email));
                if($user){
                    $app['session']->getFlashBag()->add('danger', "L'email est déjà utilisé. Voulez vous demander un nouveau mot de passe ?");
                    //return $app->redirect($app['url_generator']->generate('user.password'));
                }
                
                // Creating the user and disable it
                $user = new User();
                $user->setEmail($email);
                $user->setUsername($email);
                $encoderFactory = $app['security.encoder_factory'];
                $encoder = $encoderFactory->getEncoder($user);
                $password = $encoder->encodePassword($password, $user->getSalt());
                $user->setPassword($password);
                $user->setSite($site);
                $user->setActive(false);
                $user->setRole("ROLE_USER");

                // Generating link with token
                $token = sha1('register-request-'.$email.'-'.$timestamp.'}');
                $link = $app->url('user.register.confirm', array('token' => $token, 'email' => $email, 'timestamp' => $timestamp));
                
                // Sending link
                $text = "Bonjour,\r\n"
                        . "\r\n"
                        . "Veuillez utiliser ce lien pour confirmer votre inscription et activer votre compte : \r\n"
                        . $link . "\r\n"
                        . "\r\n"
                        . "Merci.";
                $message = \Swift_Message::newInstance()
                        ->setSubject('[Remind Me] Inscription')
                        ->setFrom(array('register@remindme.com'=>'Remind Me'))
                        ->setTo(array($user->getEmail()))
                        ->setBcc(array('amineamanzou@gmail.com'))
                        ->setBody(sprintf("%s",$text));
                if(preg_match("/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/", $user->getEmail())){
                    $em->persist($user);
                    $em->flush();
                    $app['mailer']->send($message);
                }
            }
        }

        return $app['twig']->render('user/register.twig', array(
            'form' => $form->createView(),
            'processed' => $processed,
            'link' => isset($link) ? $link : false //todo : remove
        ));
    }
    
    /**
     * @param Application $app
     * @param Request $request
     * @return mixed
     */
    public function preferences(Application $app, Request $request)
    {
        /* Traitement */

        return $app['twig']->render('user/preferences.twig');
    }
    
    /**
     * @param Application $app
     * @return mixed
     */
    public function connect(Application $app)
    {
        $index = $app['controllers_factory'];
        $index->match("/login", array($this, "login"))->bind('user.login');
        $index->match("/register", array($this, "register"))->bind('user.register');
        $index->match("/signin", array($this, "signin"))->bind('user.signin');
        $index->match("/signup", array($this, "signup"))->bind('user.signup');
        $index->match("/preferences", array($this, "preferences"))->bind('user.preferences');
        return $index;
    }
}
