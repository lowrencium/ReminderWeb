<?php
namespace App\Controller\Admin;

use Silex\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;

class AdminController implements ControllerProviderInterface
{
    
    /**
     * Change settings and password
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function preferences(Application $app, Request $request)
    {
        $em = $app["orm.em"];
        $user = $app['security']->getToken()->getUser();
        
        /** @var $builder FormBuilder */
        $builder = $app['form.factory']->createBuilder('form', $user)
            ->add('password', 'repeated', array(
                'mapped' => false,
                'type' => 'password',
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'required' => !$user->getId(),
                'first_options'  => array('label' => 'Mot de passe'),
                'second_options' => array('label' => 'Validation')
            ));
        $form = $builder->getForm();
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // password
                $password = $form->get('password')->getData();
                if($password) {
                    $encoderFactory = $app['security.encoder_factory'];
                    $encoder = $encoderFactory->getEncoder($user);
                    $password = $encoder->encodePassword($password, $user->getSalt());
                    $user->setPassword($password);
                }
                $em->persist($user);
                $em->flush();
                $app['session']->getFlashBag()->add('success', "Vos préférences ont été sauvegardées.");
                return $app->redirect($app['url_generator']->generate('admin.user'));
            }
        }

        return $app['twig']->render('admin/preferences.twig', array(
                'user' => $user,
                'form' => $form->createView()
            )
        );
    }
    
    /*
     * Index page of the admin
     * @param Application $app
     */
    public function index(Application $app)
    {
        if($app['security']->isGranted('ROLE_ADMIN')){
            return $app->redirect($app['url_generator']->generate('admin.user'));
        }
        return $app['twig']->render('admin/index.twig', array());
    }

    /*
     * Connect routes
     * @param Application $app
     */
    public function connect(Application $app)
    {
        $index = $app['controllers_factory'];
        $index->match("/", array($this, "index"))->bind('admin');
        $index->match("/preferences", array($this, "preferences"))->bind('admin.preferences');
        return $index;
    }
}
