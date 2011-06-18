<?php
require_once __DIR__.'/silex.phar';

$app = new \Silex\Application();

$app->get('/{name}', function ($name) {
    return "Hello $name";
});

//return $app;
$app->run();

