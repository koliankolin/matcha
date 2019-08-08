<?php

use Slim\App;
use Slim\Views\Twig;
use Classes\QueryBuilder;


require __DIR__ . '/../vendor/autoload.php';

session_start();

//
//// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new App($settings);

$container = $app->getContainer();

$container["qb"] = function () {
    return new QueryBuilder([
       "db" => new PDO("mysql:host=db;dbname=myDb", "root", "test")
    ]);
};

$container["db"] = function () {
  return new PDO("mysql:host=db;dbname=myDb", "root", "test");
};

//switch Twig
$container["view"] = function ($container) use($settings) {
    $view = new Twig($settings["settings"]["renderer"]["template_path"], [
        "cache" => false,
        "debug" => true
    ]);
    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));
    $view->addExtension(new \Twig\Extension\DebugExtension());
    return $view;
};