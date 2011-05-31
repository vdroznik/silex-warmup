<?php
require_once __DIR__.'/../silex.phar';
use Verse\Obituary\ObituarySearchCriterion;
use Verse\Obituary\ObituarySearchForm;

$app = new Silex\Application();

$app->register(new Silex\Extension\SymfonyBridgesExtension(), array(
    'form.class_path' => __DIR__.'/../vendor/Symfony/Bridge',
));

$app->register(new Silex\Extension\ValidatorExtension(), array(
    'validator.class_path'    => __DIR__.'/../vendor/Symfony/Component',
));

$app->register(new Silex\Extension\FormExtension(), array(
    'form.class_path' => __DIR__.'/../vendor/Symfony/Component',
));

$app->register(new Silex\Extension\TwigExtension(), array(
    'twig.path'       => __DIR__.'/../templates',
    'twig.class_path' => __DIR__.'/../vendor/twig/lib',
));


$app->match('/obituaries', function () use ($app) {
    $obituarySearchCriterion = new ObituarySearchCriterion();
    $form = $app['form.factory']->createBuilder(new ObituarySearchForm(), $obituarySearchCriterion)
//                                ->addValidator($app['validator'])
                                ->getForm();

    if($app['request']->getMethod() == 'POST') {
        $form->bindRequest($app['request']);
        $ret = $app['validator']->validate($obituarySearchCriterion);

        if($form->isValid()) {
            echo 'valid form!';
        }
    }

    return $app['twig']->render('obituaries.twig', array(
        'form' => $form->createView()
    ));
});

return $app;
