<?php
require_once __DIR__.'/../silex.phar';

use Verse\Obituary\ObituarySearchCriterion;
use Verse\Obituary\ObituarySearchForm;
use Verse\Obituary\ObituarySearcher;
use Verse\Paginator;
use Verse\PaginatorExtension;

$app = new Silex\Application();

$app->register(new Silex\Extension\SessionExtension());

$app->register(new Silex\Extension\SymfonyBridgesExtension(), array(
    'symfony_bridges.class_path' => __DIR__.'/../vendor',
));

$app->register(new Silex\Extension\DoctrineExtension(), array(
    'db.options' => array(
        'dbname' => 'verse',
        'host' => 'localhost',
        'user' => 'root',
        'password' => '`'
    ),
    'db.dbal.class_path'    => __DIR__.'/../vendor/doctrine-dbal/lib',
    'db.common.class_path'  => __DIR__.'/../vendor/doctrine-common/lib',
));

$app->register(new Silex\Extension\ValidatorExtension(), array(
    'validator.class_path'    => __DIR__.'/../vendor/Symfony/Component',
));

$app->register(new Silex\Extension\FormExtension(), array(
    'form.class_path' => __DIR__.'/../vendor/Symfony/Component',
));

$app->register(new Silex\Extension\TwigExtension(), array(
    'twig.path'       => __DIR__.'/../views',
    'twig.class_path' => __DIR__.'/../vendor/twig/lib',
    'twig.options'    => array(
//        'cache' => 'cache',
//        'debug' => true
    )
));
// $app['twig']->addFilter('paginate', new Twig_Filter_Method(new PaginatorExtension(), 'paginate'));
$app['twig']->addFilter('paginate', new Twig_Filter_Function('paginate'));
$app['twig']->addFilter('order', new Twig_Filter_Function('order'));
$app['twig']->addFilter('page', new Twig_Filter_Function('page'));

$app->before(function() use ($app, $domain_id) {
    $app['request_context']->setParameter('domain_id', $domain_id);
} );

$app->match('/obituaries-test', function () use ($app) {
    $obituarySearchCriterion = $app['session']->get('obituarySearchCriterion');
    if(!$obituarySearchCriterion) {
        $obituarySearchCriterion = new ObituarySearchCriterion($app['request_context']->getParameter('domain_id'));
    }
    $ret = $app['db']->executeQuery('SELECT DISTINCT home_place FROM plg_obituary WHERE domain_id=:domain_id ORDER BY home_place',
                                            array('domain_id'=>$app['request_context']->getParameter('domain_id')))
                     ->fetchAll(\PDO::FETCH_COLUMN);
    $home_places = array();
    foreach($ret as $home_place) {
        if(strlen(trim($home_place))>2) {
            $home_places[$home_place] = $home_place;
        }
    }
    $form = $app['form.factory']->createBuilder(new ObituarySearchForm($home_places), $obituarySearchCriterion)
//                                ->addValidator($app['validator'])
                                ->getForm();

    if($app['request']->getMethod() == 'POST') {
        // validation won't work for some reason for now
        // waiting for official FormExtension release
        $form->bindRequest($app['request']);
        // $ret = $app['validator']->validate($obituarySearchCriterion);
        if($form->isValid()) {
            $app['session']->set('obituarySearchCriterion', $obituarySearchCriterion);
        }
    }
    $obitSearcher = new ObituarySearcher($app['db'], $obituarySearchCriterion, $app['request']->get('order'), $app['request']->get('dest'));
    $paginator = new Paginator($obitSearcher, $app['request']->get('page', 1), 25);

    return $app['twig']->render('obituaries.twig', array(
        'form' => $form->createView(),
        'paginator' => $paginator
    ));
});

return $app;

function paginate(Paginator $paginator) {
    global $obituaries;

    return $obituaries['twig']->render('paginator.twig', array(
        'paginator' => $paginator
    ));
}

function order($link_text, $order_by) {
    global $obituaries;

    $page = $obituaries['request']->get('page');
    $order = $obituaries['request']->get('order');
    $order_dest = $obituaries['request']->get('dest');

    if($order == $order_by) {
        if($order_dest=='desc') {
            $order_dest = '';
        }
        else {
            $order_dest = 'desc';
        }
    }
    else {
        $order_dest = '';
    }

    if($page) {
        $page="page=$page&";
    }

    if($order_dest) {
        $order_dest = '&dest='.$order_dest;
    }
    
    return "<a href=\"?{$page}order=$order_by$order_dest\">$link_text</a>";
}

function page($link_text, $page = null) {
    global $obituaries;

    if(!$page) {
        $page = $link_text;
    }

    $order = $obituaries['request']->get('order');
    $order_dest = $obituaries['request']->get('dest');

    if($order) {
        $order = "&order=$order";
    }
    if($order_dest) {
        $order_dest = "&dest=$order_dest";
    }

    return "<a href=\"?page=$page$order$order_dest\">$link_text</a>";
}
