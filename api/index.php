<?php
// Database connection settings
const DATABASE_CONFIG = [
    "host"     => "database",
    "dbname"   => "probafeladat",
    "username" => "teszt",
    "password" => "elek",
    "options"  => [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    ],
];

// Don't edit below this line
use Phalcon\Di\FactoryDefault;
use Phalcon\Http\ResponseInterface;
use Phalcon\Autoload\Loader;
use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Phalcon\Mvc\Application;
use Phalcon\Db\Adapter\Pdo\Postgresql;

date_default_timezone_set("Europe/Budapest");

try {
    $container = new FactoryDefault();

    $container->setShared("db", function () {
        return new Postgresql(DATABASE_CONFIG);
    });

    // Register loader
    $loader = new Loader();
    $loader->setDirectories([
        __DIR__ . "/controllers/",
        __DIR__ . "/modules/",
    ]);
    $loader->register();

    // View setup
    $container->set("view", function () {
        $view = new \Phalcon\Mvc\View();
        $view->setViewsDir(__DIR__ . "/views/");
        return $view;
    });

    // Response setup
    $container->set("response", function () {
        $response = new Response();
        $response->setHeader("Access-Control-Allow-Origin", "*");
        $response->setHeader("Access-Control-Allow-Methods", "GET, POST, PATCH, PUT, DELETE, OPTIONS");
        $response->setHeader("Access-Control-Allow-Headers", "Content-Type, Authorization, Origin, X-Auth-Token");
        return $response;
    });

    // Request setup
    $request = new Request();
    $container->set("request", function () use ($request) {
        return $request;
    });

    $dispatcher = $container->get('dispatcher');
    $container->set('dispatcher', function () use ($dispatcher) {
        return $dispatcher;
    });

    $app = new Application($container);

    $response = $app->handle($request->getUri());

    if ($response instanceof ResponseInterface) {
        $response->send();
    } else {
        throw new Exception("Invalid response: " . $response);
    }
} catch (Exception $e) {
    $response = new Response();
    $response->setStatusCode(500)
        ->setJsonContent(["error" => $e->getMessage()])
        ->send();
}
