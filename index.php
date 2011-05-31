<?php
$obituaries = require __DIR__.'/src/obituaries.php';

$app = new \Silex\Application();
$app['autoloader']->registerNamespace('Verse', 'src');
$app['autoloader']->registerNamespace('Symfony', 'vendor');

$app->mount('/', $obituaries);

$app->run();
