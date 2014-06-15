<?php
namespace App\Controller\Admin;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Validator\Constraints as Assert;
use Silex\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query;
use App\Entity\User;
use Volcanus\Csv\Writer;
use Doctrine\ORM\EntityRepository;

/**
 * Class UserController
 * @package App\Controller\Admin
 */
class UserController implements ControllerProviderInterface
{
    /**
     * List the users
     * @param Application $app
     * @param Request $request
     * @return mixed
     */
    public function index(Application $app, Request $request)
    {
        /** @var EntityManager $em */
        $repository = $app["orm.em"]->getRepository('App\Entity\User');

        /** @var FormFactory $factory */
        $factory = $app['form.factory'];
        $builder = $factory->createBuilder('form')
          ->add('username', 'text', array(
              'label' => 'Filtrer par Email',
              'required' => false,
          ));
        $builder->setMethod("get");
        $form = $builder->getForm();
        $form->handleRequest($request);
        // Preparing the query
        $filterBuilder = $app["orm.em"]->createQueryBuilder('u')
                                        ->select('u')
                                        ->from('App\Entity\User', 'u');
        if($form->isValid()){
            if (!empty($data['username']))
            {
                $filterBuilder->andWhere("u.username LIKE :username")
                              ->setParameter('username', '%'.$data['username'].'%');
            }
        }
        $users = $filterBuilder->getQuery()->execute();

        
        // render template
        return $app['twig']->render('admin/user/index.twig', array(
            'users' => $users,
            'form' => $form->createView()
        ));
    }

    /**
     * List the users history login
     * @param Application $app
     * @param Request $request
     * @return mixed
     */
    public function history(Application $app, Request $request)
    {
        $em = $app["orm.em"];
        $id = $request->get('id');
        $user = $em->find('App\Entity\User', $id);
        if(!$user) {
            $app['session']->getFlashBag()
                           ->add('danger', "Cet utilisateur n'existe pas.");
            return $app->redirect($app['url_generator']->generate('admin.user'));
        }
        $userRoles = $user->getRoles();
        if(!$app['security']->isGranted('ROLE_SUPER_ADMIN') && in_array('ROLE_SUPER_ADMIN',$userRoles)){
            $app['session']->getFlashBag()
                           ->add('danger', "Cet utilisateur n'existe pas.");
            return $app->redirect($app['url_generator']->generate('admin.user'));
        }
        return $app['twig']->render('admin/user/history.twig', array(
            'user' => $user,
            'history' => $user->getLogins(),
        ));
    }
    
    
    /**
     * Edit a specific user
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function edit(Application $app, Request $request)
    {
        $em = $app["orm.em"];
        $id = $request->get('id');
        $isSelf = $id == $app['security']->getToken()->getUser()->getId();

        $listRoles = array(
            'ROLE_USER' => 'Utilisateur',
            'ROLE_ORGANISATION' => 'Organisation',
            'ROLE_ADMIN' => 'Administrateur'
        );

        if (!empty($id)) {
            $user = $em->find('App\Entity\User', $id);
            if (!$user) {
                throw new NotFoundHttpException("Cet utilisateur n'existe pas");
            }
        } else {
            $user = new User();
        }
        /** @var $builder FormBuilder */
        $builder = $app['form.factory']->createBuilder('form', $user)
            ->add('username', 'text',
                array(
                    'label' => "Nom d'utilisateur",
                    'attr' => array('class' => 'input-xxlarge')
                )
            )
            ->add('email', 'text',
                array(
                    'label' => 'E-mail',
                    'constraints' => new Assert\Email(),
                    'attr' => array('class' => 'input-xxlarge')
                )
            )
            ->add('roles', 'choice',
                array(
                    'label' => 'Rôles',
                    'multiple' => true,
                    'expanded' => true,
                    'choices' => $listRoles
                )
            )
            ->add('password', 'repeated', array(
                'mapped' => false,
                'type' => 'password',
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'required' => !$user->getId(),
                'first_options'  => array('label' => 'Mot de passe'),
                'second_options' => array('label' => 'Validation')
            ));
        
        $builder->add('active', 'checkbox',
                array(
                  'label' => 'Compte actif',
                  'disabled' => $isSelf,
                  'required' => false
                )
        );
        
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
                $app['session']->getFlashBag()->add('success', "L'utilisateur <em>".$user->getUsername()."</em> a bien été sauvegardé.");
                return $app->redirect($app['url_generator']->generate('admin.user.edit', array("id" => $user->getId())));
            }
        }

        return $app['twig']->render('admin/user/edit.twig', array(
                'user' => $user,
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Add a new user
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function add(Application $app, Request $request)
    {
        $siteFormParams = array(
            'label' => 'Site',
            'multiple' => false,
            'empty_value' => "Choisissez un site",
            'empty_data' => null,
            'required' => true,
            'class' => 'App\Entity\Site'
        );
        $listRoles = array(
            'ROLE_USER' => 'Utilisateur',
            'ROLE_ORGANISATION' => 'Organisation',
            'ROLE_ADMIN' => 'Administrateur'
        );
        // Checking permission
        if (!$app['security']->isGranted('ROLE_ADMIN')) {
            $app['session']->getFlashBag()->add('danger', "Vous n'avez pas les droits de création.");
            return $app->redirect($app['url_generator']->generate('admin.user'));
        }
        else if($app['security']->isGranted('ROLE_SUPER_ADMIN')){
            $listRoles['ROLE_SUPER_ADMIN'] = 'Super Administrateur';
        }

        $em = $app["orm.em"];
        $user = new User();

        /** @var $builder FormBuilder */
        $builder = $app['form.factory']->createBuilder('form', $user)
            ->add('username', 'text',
                array(
                    'label' => "Nom d'utilisateur",
                    'attr' => array('class' => 'input-xxlarge')
                )
            )
            ->add('email', 'text',
                array(
                    'label' => 'E-mail',
                    'constraints' => new Assert\Email(),
                    'attr' => array('class' => 'input-xxlarge')
                )
            )
            ->add('password', 'repeated', array(
                'mapped' => false,
                'type' => 'password',
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'required' => !$user->getId(),
                'first_options'  => array('label' => 'Mot de passe'),
                'second_options' => array('label' => 'Validation')
            ))
            ->add('roles', 'choice',
                array(
                    'label' => 'Rôles',
                    'multiple' => true,
                    'expanded' => true,
                    'choices' => $listRoles
                )
            )
            ->add('active', 'checkbox',
                array(
                  'label' => 'Compte actif',
                  'required' => false
                )
          );
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
                $app['session']->getFlashBag()->add('success', "L'utilisateur <em>".$user->getUsername()."</em> a bien été sauvegardé.");
                return $app->redirect($app['url_generator']->generate('admin.user.edit', array("id" => $user->getId())));
            }
        }

        return $app['twig']->render('admin/user/edit.twig', array(
                'user' => $user,
                'form' => $form->createView()
            )
        );
    }

    /**
     * Delete a user and his game data
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function delete(Application $app, Request $request)
    {
        // Checking permission
        if (!$app['security']->isGranted('ROLE_ADMIN')) {
            $app['session']->getFlashBag()->add('danger', "Vous n'avez pas les droits de suppression.");
            return $app->redirect($app['url_generator']->generate('admin.user'));
        }

        $em = $app["orm.em"];
        $id = $request->get('id');
        $isSelf = $id == $app['security']->getToken()->getUser()->getId();

        if (!empty($id)) {
            $user = $em->find('App\Entity\User', $id);
            if (!$user) {
                throw new NotFoundHttpException("Cet utilisateur n'existe pas");
            }
        }

        if($isSelf) {
            throw new NotAcceptableHttpException("Vous ne pouvez pas supprimer cet utilisateur.");
        }

        if ($request->isMethod('POST')) {
            $em->remove($user);
            $em->flush();
            $app['session']->getFlashBag()->add('success', "L'utilisateur <em>".$user->getUsername()."</em> a bien été supprimé.");
            return $app->redirect($app['url_generator']->generate('admin.user'));
        }

        return $app['twig']->render('admin/user/delete.twig', array('user' => $user));
    }
    
    /**
     * MySql equivalent to convert seconds to duration
     */
    private function  sec_to_time($seconds){
        $hours = floor($seconds / 3600);
        return $hours.':'.date('i:s',$seconds);
    }
    
    /**
     * @param Application $app
     * @return mixed
     */
    public function connect(Application $app)
    {
        $index = $app['controllers_factory'];
        $index->match("/", array($this, "index"))->bind('admin.user');
        $index->match("/add", array($this, "add"))->bind('admin.user.add');
        $index->match("/{id}", array($this, "edit"))->bind('admin.user.edit');
        $index->match("/{id}/history", array($this, "history"))->bind('admin.user.history');
        $index->match("/{id}/delete", array($this, "delete"))->bind('admin.user.delete');
        return $index;
    }
}
