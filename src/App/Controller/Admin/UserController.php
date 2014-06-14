<?php
/**
 * Auteur: Blaise de Carné - blaise@concretis.com
 */
namespace App\Controller\Admin;

use App\Controller\StatisticsController;
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
use App\Entity\Situation;
use App\Entity\Step;
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

        // site filter
        $siteFormParams = array(
            'label' => 'Filtrer par site',
            'required' => false,
            'multiple' => false,
            'class' => 'App\Entity\Site',
            'empty_value' => 'Tous les sites',
            'empty_data' => NULL
        );
        if(!$app['security']->isGranted('ROLE_SUPER_ADMIN')){
                    $siteFormParams['query_builder'] =  function(EntityRepository $er) {
                                                            return $er->createQueryBuilder('s')
                                                                ->where('s.id != :id')
                                                                ->setParameter('id',1)
                                                                ->orderBy('s.name','ASC');
                                                        };
        }
        /** @var FormFactory $factory */
        $factory = $app['form.factory'];
        $builder = $factory->createBuilder('form')
          ->add('site', 'entity',
              $siteFormParams
          )
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
        // Checking permission
        if($app['security']->isGranted('ROLE_SUPER_ADMIN')){
            
            if($form->isValid()) {
                $data = $form->getData();
                if (!empty($data['username']))
                {
                    $filterBuilder->andWhere("u.username LIKE :username")
                                  ->setParameter('username', '%'.$data['username'].'%');
                }
                if (!empty($data['site']))
                {
                    $filterBuilder->andWhere("u.site = :site")
                                  ->setParameter('site', $data['site']);
                }
                
            }
        }
        else if($app['security']->isGranted('ROLE_ADMIN')){
            
            if($form->isValid()){
                $data = $form->getData();
                if (!empty($data['username']))
                {
                    $filterBuilder->andWhere("u.username LIKE :username")
                                  ->setParameter('username', '%'.$data['username'].'%');
                }
                if (!empty($data['site']))
                {
                    $filterBuilder->andWhere("u.site = :site")
                                  ->setParameter('site', $data['site']);
                }
            }
            $filterBuilder->andWhere("u.roles != :role")
                          ->setParameter('role', 'ROLE_SUPER_ADMIN')
                          ->andWhere("u.site != :nosite OR u.site IS NULL")
                          ->setParameter('nosite', 1);
        }
        else if($app['security']->isGranted('ROLE_MANAGER')) {
            $site = $app['security']->getToken()->getUser()->getSite();
            if($form->isValid()){
                if (!empty($data['username']))
                {
                    $filterBuilder->andWhere("u.username LIKE :username")
                                  ->setParameter('username', '%'.$data['username'].'%');
                }
            }
            $filterBuilder->andWhere("u.site = :site")
                          ->setParameter('site', $site)
                          ->andWhere("u.roles != :role")
                          ->setParameter('role', 'ROLE_SUPER_ADMIN');
        }
        $users = $filterBuilder->getQuery()->execute();
        // Calculating relative time
        foreach($users as $user){
            $timing = 0;
            $situations = $user->getSituations();
            foreach($situations as $situation){
                $timing += $situation->getTiming();
            }
            $user->timing = $this->sec_to_time($timing);
        }
        // Display flash if user don't have a site
        $userSite = $app['security']->getToken()->getUser()->getSite();
        if(empty($userSite)){
            $app['session']->getFlashBag()
                           ->add('danger', "Veuillez sélectionner un site dans les préférences (Accueil > Préférences)");
        }
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
     * List the users situations
     * @param Application $app
     * @param Request $request
     * @return mixed
     */
    public function situation(Application $app, Request $request)
    {
        $em = $app["orm.em"];
        $id = $request->get('id');
        $user = $em->find('App\Entity\User', $id);
        $situations = $app["orm.em"]->createQueryBuilder()
                                    ->select('sit')
                                    ->from('App\Entity\Situation', 'sit')
                                    ->orderBy('sit.position', 'ASC')
                                    ->getQuery()->getArrayResult();
        //echo "<pre>";print_r($situations);echo "</pre>";
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
        
        $qb = $em->createQueryBuilder()
          ->select('s, us')
          ->from('App\\Entity\\Situation', 's')
          ->leftJoin('s.users', 'us', 'WITH', 'us.user = :user')
          ->setParameter('user', $user)
          ->orderBy('s.position');
        $situationEntities = $qb->getQuery()->getResult();
        
        $situations = array();
        foreach($situationEntities as $situation) {
            $situations[] = $this->getSituationArray($app, $situation);
        }
        
        return $app['twig']->render('admin/user/situation.twig', array(
            'situations' => $situations,
        ));
    }
    
    /**
     * Return the situation array with steps and userstep based on current user
     *
     * @param Application $app
     * @param Situation $situation
     * @param bool $getSlides
     * @return mixed
     */
    private function getSituationArray(Application $app, Situation $situation)
    {
        $userSituation = $situation->getUsers()->get(0);

        // construct a query to get user info with each step
        $qb = $app["orm.em"]->createQueryBuilder()
          ->select('s, us')
          ->from('App\\Entity\\Step', 's')
          ->leftJoin('s.users', 'us', 'WITH', 'us.user = :user')
          ->where('s.situation = :situation')
          ->orderBy('s.position')
          ->setParameter('user', $userSituation ? $userSituation->getUser() : null)
          ->setParameter('situation', $situation);

        // get the results
        $results = $qb->getQuery()->getResult();

        // construct the step array
        $steps = array();
        $success = 0;
        foreach($results as $step) {
            $serializedStep = $this->serializeStepResult($app, $step);
            // if the step is completed, increment success count
            if($serializedStep['success']){
                $success++;
            }
            $steps[] = $serializedStep;
        }
        $progress = round($success / count($steps) * 100);
        
        return array(
            'id' => $situation->getId(),
            'name' => $situation->getName(),
            'type' => $situation->getType(),
            'level' => $situation->getLevel(),
            'timing' => $userSituation ? $this->sec_to_time($userSituation->getTiming()) : $this->sec_to_time(0),
            'timeLimit' => ($situation->getTimeLimit() != 0) ? $this->sec_to_time($situation->getTimeLimit()) : '-',
            'nbStepsDone' => $success,
            'nbSteps' => count($steps),
            'progress' => $progress,
            'done' => $userSituation ? 1 : 0,
            'success' => $userSituation ? $userSituation->getSuccess() : false,
            'steps' => $steps
        );
    }

    /**
     * Return the step array with user_step based on current user
     *
     * @param Application $app
     * @param Step $result
     * @param bool $getSlides
     * @return mixed
     */
    private function serializeStepResult(Application $app, Step $result)
    {
        $userStep = $result->getUsers()->get(0);

        $step = array(
            'id' => $result->getId(),
            'name' => $result->getName(),
            'type' => $result->getType(),
            'timing' => $userStep ? $userStep->getTiming() : 0,
            'success' => $userStep ? $userStep->getSuccess() : false
        );
        
        return $step;
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

        // Site selector
        $siteFormParams = array(
            'label' => 'Site',
            'multiple' => false,
            //'disabled' => $isSelf,
            'empty_value' => "Choisissez un site",
            'empty_data' => null,
            'required' => true,
            'class' => 'App\Entity\Site',
        );
        $listRoles = array(
            'ROLE_USER' => 'Utilisateur',
            'ROLE_MANAGER' => 'Manager',
            'ROLE_ADMIN' => 'Administrateur'
        );
        // Checking permission
        if(!$app['security']->isGranted('ROLE_SUPER_ADMIN')){
            $siteFormParams['query_builder'] =  function(EntityRepository $er) {
                                                            return $er->createQueryBuilder('s')
                                                                ->where('s.name NOT LIKE :name')
                                                                ->setParameter('name','%Lacompany%');
                                                        };
        }
        else {
            $listRoles['ROLE_SUPER_ADMIN'] = 'Super Administrateur';
        }
        
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
            ->add('site', 'entity',
                $siteFormParams
            )
            ->add('password', 'repeated', array(
                'mapped' => false,
                'type' => 'password',
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'required' => !$user->getId(),
                'first_options'  => array('label' => 'Mot de passe'),
                'second_options' => array('label' => 'Validation')
            ));
        if ($app['security']->isGranted('ROLE_ADMIN')) {
            $builder->add('level', 'choice',
                    array(
                      'label' => 'Niveau',
                      'choices' => array(
                          '0' => 'Intégrant',
                          '1' => 'Ambassadeur',
                          '2' => 'Expert',
                      )
                    )
                )
                ->add('roles', 'choice',
                    array(
                        'label' => 'Rôles',
                        'multiple' => true,
                        'disabled' => $isSelf,
                        'expanded' => true,
                        'choices' => $listRoles
                    )
                )
                ->add('active', 'checkbox',
                    array(
                      'label' => 'Compte actif',
                      'disabled' => $isSelf,
                      'required' => false
                    )
                );
        }
        else {
            $builder->add('active', 'checkbox',
                    array(
                      'label' => 'Compte actif',
                      'disabled' => $isSelf,
                      'required' => false
                    )
                );
        }
        $form = $builder->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // site
                if (!$app['security']->isGranted('ROLE_ADMIN')) {
                    if($isSelf){
                        $user->setSite($app['security']->getToken()->getUser()->getSite());
                    }
                }
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
                'isSelf' => $isSelf
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
            'ROLE_MANAGER' => 'Manager',
            'ROLE_ADMIN' => 'Administrateur'
        );
        // Checking permission
        if (!$app['security']->isGranted('ROLE_ADMIN')) {
            $app['session']->getFlashBag()->add('danger', "Vous n'avez pas les droits de création.");
            return $app->redirect($app['url_generator']->generate('admin.user'));
        }
        else if(!$app['security']->isGranted('ROLE_SUPER_ADMIN')){
                    $siteFormParams['query_builder'] =  function(EntityRepository $er) {
                                                            return $er->createQueryBuilder('s')
                                                                ->where('s.name NOT LIKE :name')
                                                                ->setParameter('name','%Lacompany%');
                                                        };
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
            ->add('site', 'entity',
                $siteFormParams
            )
            ->add('password', 'repeated', array(
                'mapped' => false,
                'type' => 'password',
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'required' => !$user->getId(),
                'first_options'  => array('label' => 'Mot de passe'),
                'second_options' => array('label' => 'Validation')
            ))
            ->add('level', 'choice',
                array(
                  'label' => 'Niveau',
                  'choices' => array(
                      '0' => 'Intégrant',
                      '1' => 'Ambassadeur',
                      '2' => 'Expert',
                  )
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
     * Display statistics about user game progression
     * @param \Silex\Application $app
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function statistics(Application $app, Request $request)
    {
        $em = $app["orm.em"];
        $id = $request->get('id');
        $user = $em->find('App\Entity\User', $id);
        if(!$user) {
            return new NotFoundHttpException('This user does not exits.');
        }

        $statistics = array();
        $modules = $em->getRepository('App\Entity\Module')->findBy(array(), array('id' => 'ASC'));
        foreach($modules as $module) {
            $qb = $app["orm.em"]->createQueryBuilder();
            $qb->select('t')
              ->from('App\\Entity\\Tag', 't')
              ->leftJoin('t.steps', 's')
              ->leftJoin('s.situation', 'sit')
              ->where('t.module = :module')->setParameter('module', $module)
              ->andWhere('s.type = :type')->setParameter('type', 'practice')
              ->andWhere('sit.type = :typeSit')->setParameter('typeSit', 'evaluation');
            $tags = $qb->getQuery()->getResult();

            $stats = &$statistics[$module->getId()];
            $stats = array(
                "tags" => array(), "progress" => 0
            );
            foreach($tags as $tag) {
                $progress = StatisticsController::getUserProgressByTag($app, $user, $tag);
                $stats["tags"][$tag->getId()] = $progress;
                $stats["progress"] += $progress;
            }
            if(count($tags)) {
                $stats["progress"] = $stats["progress"] / count($tags);
            }
        }

        return $app['twig']->render('admin/user/statistics.twig', array(
            'user' => $user,
            'modules' => $modules,
            'statistics' => $statistics
        ));
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
     * Export a csv with the list of user
     * @param Application $app
     * @param Request $request
     */
    public function export(Application $app, Request $request)
    {
        // Checking permission
        if (!$app['security']->isGranted('ROLE_ADMIN')) {
            $app['session']->getFlashBag()->add('danger', "Vous n'avez pas les droits d'export.");
            return $app->redirect($app['url_generator']->generate('admin.user'));
        }

        /** @var EntityManager $em */
        $em = $app["orm.em"];
        $qb = $em->createQueryBuilder()
          ->select('u, us, s')
          ->from('App\\Entity\\User', 'u')
          ->leftJoin('u.site', 's')
          ->leftJoin('u.situations', 'us');
        $users = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);

        $users = array_map(function($user) {
            $levels = array("Intégrant", "Ambassadeur", "Expert");

            $user['level'] = $levels[$user['level']];
            $user['created'] = $user['created']->format('Y-m-d H:i');
            $user['lastLogin'] = $user['lastLogin'] ? $user['lastLogin']->format('Y-m-d H:i') : null;
            $user['site_name'] = $user['site']['name'];
            $user['site_id'] = $user['site']['id'];

            return $user;
        }, $users);

        $writer = new Writer(array(
            'delimiter'        => ';',
            'enclose'          => true,
            'enclosure'        => '"',
            'escape'           => '"',
            'inputEncoding'    => 'UTF-8',
            'outputEncoding'   => 'ISO-8859-1',
            'writeHeaderLine'  => true,
            'responseFilename' => 'nessoft-users.csv',
        ));
        $writer->fields(array(
            array('id', 'Identifiant'),
            array('username', "Nom d'utilisateur"),
            array('email', 'Email'),
            array('site_name', 'Site'),
            array('site_id', 'Site ID'),
            array('level', 'Niveau'),
            array('active', 'Actif'),
            array('created', 'Date de création'),
            array('lastLogin', 'Dernière connexion'),
            array('nbLogin', 'Nombre de connexion')
        ));

        $file = new \SplFileObject('php://temp', 'r+');
        $writer->setFile($file);
        $writer->write($users);
        $writer->send();
        exit;
    }

    /**
     * Fix the user level to the last evaluation he done
     * 
     * @param Application $app
     * @param Request $request
     */
    public function fixuserlevel(Application $app, Request $request){
        $result = array();
        $repository = $app["orm.em"]->getRepository('App\Entity\Situation');
        // Preparing the query
        $filterBuilder = $app["orm.em"]->createQueryBuilder('u')
                                        ->select('u')
                                        ->from('App\Entity\User', 'u');
        $users = $filterBuilder->getQuery()->execute();
        // Calculating relative time
        foreach($users as $user){
            $levelMax = 0;
            $situations = $user->getSituations();
            foreach($situations as $situation){
                $situation = $repository->find($situation->getSituation());
                // Getting the maximum reached level
                if($situation->getType() == "evaluation" && $situation->getLevel() > $levelMax){
                    $levelMax = $situation->getLevel();
                }
            }
            // Generating a report with user list
            if($user->getLevel() != $levelMax) {
                $result[] = array(
                    "id" => $user->getId(),
                    "user" => $user->getUsername(),
                    "ulevel" => $user->getLevel(),
                    "slevel" => $levelMax,
                );
                // Fixing the user
                $user->setLevel($levelMax);
                $app["orm.em"]->persist($user);
            }
        }
        // Saving data
        $app["orm.em"]->flush();
        return $app->json($result);
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
        $index->match("/fixuserlevel", array($this, "fixuserlevel"))->bind('admin.user.fixuserlevel');
        $index->match("/export", array($this, "export"))->bind('admin.user.export');
        $index->match("/add", array($this, "add"))->bind('admin.user.add');
        $index->match("/{id}", array($this, "edit"))->bind('admin.user.edit');
        $index->match("/{id}/history", array($this, "history"))->bind('admin.user.history');
        $index->match("/{id}/situation", array($this, "situation"))->bind('admin.user.situation');
        $index->match("/{id}/statistics", array($this, "statistics"))->bind('admin.user.statistics');
        $index->match("/{id}/delete", array($this, "delete"))->bind('admin.user.delete');
        return $index;
    }
}
