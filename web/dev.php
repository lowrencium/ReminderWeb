<?php
ini_set('display_errors', 1);
require_once __DIR__.'/../vendor/autoload.php';

\Symfony\Component\ClassLoader\DebugClassLoader::enable();
\Symfony\Component\HttpKernel\Debug\ErrorHandler::register();
\Symfony\Component\HttpKernel\Debug\ExceptionHandler::register();

$app = new App\Application(true);
$app->register(new \Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => __DIR__ . '/../var/logs/dev.log',
));
$app->run();