<?php

namespace App;

use App\Doctrine\Persistence\ManagerRegistry;
use App\Entity\UserRepository;
use App\Entity\UserLogin;
use App\Routing\Generator\UrlGenerator;
use Silex\Application as SilexApplication;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Entea\Twig\Extension\AssetExtension;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Silex\Provider\SwiftmailerServiceProvider;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Yosymfony\Silex\ConfigServiceProvider\ConfigServiceProvider;

class Application extends SilexApplication
{
    use SilexApplication\UrlGeneratorTrait;

    public function __construct($debug = false)
    {
        parent::__construct();
        $app = $this;
        $this["debug"] = $debug;

        # Loading Config file
        $app->register(new ConfigServiceProvider());
        $config = $app['configuration']->load(__DIR__ . "/../../config/config.yml");

        # Url generator
        $this->register(new UrlGeneratorServiceProvider());

        # Webservices
        $this->after(function (Request $request, Response $response) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        });
        
        # Validator
        $this->register(new ValidatorServiceProvider(), array(
            'validator.validator_service_ids' => array('doctrine.orm.validator.unique' => 'security.validator.unique_entity_validator')
        ));

        # Twig templating
        $this->register(
            new TwigServiceProvider(),
            array(
                'twig.path' => array(__DIR__ . '/../../templates'),
                'twig.options' => array('cache' => __DIR__ . '/../../var/cache'),
                'twig.form.templates' => array('form.twig'),
            )
        );
        $this['twig'] = $this->share($this->extend('twig', function($twig, $app) {
            $twig->addExtension(new AssetExtension($app));
            $twig->addExtension(new \Twig_Extensions_Extension_Text());
            $twig->addGlobal('debug', $app['debug']);
            $twig->addGlobal('user', $app['security']->getToken()->getUser());
            return $twig;
        }));

        # Form
        $this->register(new FormServiceProvider());

        # Session
        $this->register(new SessionServiceProvider(), array(
            "session.storage.options" => array(
                "cookie_lifetime" => 0,
                "gc_maxlifetime" => 3600
            )
        ));

        # Localisation
        $this->register(new TranslationServiceProvider(), array("locale" => "fr"));

        # Url Generator
        $app['url_generator'] = $app->share(function ($app) {
            $app->flush();
            return new UrlGenerator($app['routes'], $app['request_context']);
        });

        # Email config
        $this->register(new SwiftmailerServiceProvider(), array(
            'swiftmailer.options' => $config->get('mail', array())
        ));

        # Doctrine DBAL
        $this->register(
            new DoctrineServiceProvider(),
            array(
                'db.options' => array(
                    'driver' => $config['db']['driver'],
                    'host' => $config['db']['host'],
                    'dbname' => $config['db']['dbname'],
                    'user' => $config['db']['user'],
                    'password' => $config['db']['password'],
                    'driverOptions' => array(1002 => "SET NAMES 'UTF8'")
                ),
            )
        );

        # Doctrine ORM
        $app->register(new DoctrineOrmServiceProvider(), array(
            "orm.proxies_dir" => __DIR__ . '/../../cache/doctrine/proxies',
            "orm.em.options" => array(
                "mappings" => array(
                    // Using actual filesystem paths
                    array(
                        "type" => 'annotation',
                        "namespace" => 'App\Entity',
                        "path" => __DIR__ . '/../App/Entity',
                        "use_simple_annotation_reader" => false
                    )
                ),
            ),
        ));

        // doctrine ORM ManagerRegistry
        $app['orm.manager_registry'] = $app->share(function($app) {
            $managerRegistry = new ManagerRegistry(null, array(), array('orm.em'), null, null, 'Doctrine\ORM\Proxy\Proxy');
            $managerRegistry->setContainer($app);
            return $managerRegistry;
        });

        // Form : doctrine orm extension
        $app['form.extensions'] = $app->share($this->extend('form.extensions', function ($extensions) use ($app) {
            $extensions[] = new DoctrineOrmExtension($app['orm.manager_registry']);
            return $extensions;
        }));

        // orm unique entity validator
        $app['security.validator.unique_entity_validator'] = $app->share(function ($app) {
            return new UniqueEntityValidator($app['orm.manager_registry']);
        });

        // Security
        $this->register(
            new SecurityServiceProvider(),
            array(
                'security.role_hierarchy' => array(
                    'ROLE_SUPER_ADMIN' => array('ROLE_ADMIN'),
                    'ROLE_ADMIN' => array('ROLE_ORGANISATION'),
                    'ROLE_ORGANISATION' => array('ROLE_USER'),
                    'ROLE_USER' => array('ROLE_INVITED')
                ),
                'security.firewalls' => array(
                    'secured' => array(
                        'pattern' => '^/',
                        'anonymous' => array(),
                        'form' => array(
                            'login_path' => "/user/login",
                            'check_path' => "/user/dologin",
                            "default_target_path" => "/",
                            //"always_use_default_target_path" => true,
                            'username_parameter' => 'login[username]',
                            'password_parameter' => 'login[password]',
                            "csrf_parameter" => "login[_token]",
                            "failure_path" => "/",
                        ),
                        'logout' => array(
                            'logout_path' => "/user/logout",
                            "target" => '/',
                            "invalidate_session" => true,
                            "delete_cookies" => array()
                        ),
                        'users' => $this->share(function () use ($app) {
                            return new UserRepository($app['orm.em'], $app['orm.em']->getClassMetadata('App\Entity\User'));
                        }),
                    ),
                ),
                'security.access_rules' => array(
                    array('^/user/login', 'IS_AUTHENTICATED_ANONYMOUSLY'),
                    array('^/user/register', 'IS_AUTHENTICATED_ANONYMOUSLY'),
                    array('^/user/password', 'IS_AUTHENTICATED_ANONYMOUSLY'),
                    array('^/user/logout', 'IS_AUTHENTICATED_ANONYMOUSLY'),
                    array('^/admin/$', 'ROLE_ORGANISATION'),
                    array('^.*$', 'IS_AUTHENTICATED_ANONYMOUSLY'),
                )
            )
        );
        
        # log des connexions
        $this['dispatcher']->addListener('security.interactive_login', function (InteractiveLoginEvent $event) use ($app) {
            if(strcmp($app['request']->getPathInfo(),"/user/dologin") === 0){
                /** @var User $user */
                $user = $app['security']->getToken()->getUser();
                $userLogin = new UserLogin();
                $userLogin->setUser($user);
                $userLogin->setAction("Login");
                $userLogin->setContext("Web Application");
                $user->setLastLogin($userLogin->getCreated());
                $user->setNbLogin($user->getLogins()->count() + 1);
                $user->setToken(hash('sha256',$user->getUsername().time()));
                $user->setSessionExpire(date("Y-m-d H:i:s", strtotime( date('Y-m-d H:i:s') . " + 8 hours")));
                $app['orm.em']->persist($userLogin);
                $app['orm.em']->persist($user);
                $app['orm.em']->flush();
                $app['session']->getFlashBag()->add('success', "Vous êtes bien connecté.");
            }
        });
        
        # Managing Errors
        $this->error(function (\Exception $e, $code) use ($app) {
            if ($app['debug']) {
                return;
            }

            $page = 404 == $code ? 'errors/404.twig' : 'errors/500.twig';
            return new Response($app['twig']->render($page, array('code' => $code)), $code);
        });

        # Mounting controllers
        $this->setRoutes();
    }

    /*
     * setRoutes
     */
    function setRoutes() {
        // Frontend
        $this->mount("/", new \App\Controller\IndexController());
        $this->mount("/user", new \App\Controller\UserController());
        $this->mount("/calendar", new \App\Controller\RappelController());
        $this->mount("/contact", new \App\Controller\ContactController());

        // Backend
        $this->mount("/admin", new \App\Controller\Admin\AdminController());
        $this->mount("/admin/user", new \App\Controller\Admin\UserController());

    }   
}
