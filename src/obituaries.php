<?php
include_once __DIR__.'/../inc/imageutil.inc.php';
require_once __DIR__.'/../silex.phar';
require_once __DIR__.'/../config/config.php';

use Verse\Obituary\ObituarySearchCriterion;
use Verse\Obituary\ObituarySearchForm;
use Verse\Obituary\ObituarySearcher;
use Verse\Obituary\ObituaryHomePlaces;
use Verse\Paginator;
use Verse\Domain\Domain;

$app = new Silex\Application();
$app['debug'] = true;

$app->register(new Silex\Extension\SessionExtension());

$app->register(new Silex\Extension\SymfonyBridgesExtension(), array(
    'symfony_bridges.class_path' => __DIR__.'/../vendor',
));

$app->register(new Silex\Extension\DoctrineExtension(), array(
    'db.options' => array(
        'dbname' => Config\DB\NAME,
        'host' => Config\DB\HOST,
        'user' => Config\DB\USER,
        'password' => Config\DB\PASS
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
//    'cache' => 'cache',
//    'debug' => true
    )
));

$app['twig']->addFilter('paginate', new Twig_Filter_Function('paginate'));
$app['twig']->addFilter('order', new Twig_Filter_Function('order'));
$app['twig']->addFilter('page', new Twig_Filter_Function('page'));
$app['twig']->addFilter('truncate', new Twig_Filter_Function('truncate'));
$app['twig']->addFilter('thumbnail', new Twig_Filter_Function('thumbnail'));

$app->before(function() use ($app, $domain_id) {
    // we have domain_id from global namespace
    $domain = new Domain($app['db'], $domain_id);
    $app['request_context']->setParameter('domain', $domain);
} );

$app->match('/online-obituary', function () use ($app) {
//    $app['db.config']->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());
    $domain = $app['request_context']->getParameter('domain');
    $obituarySearchCriterion = $app['session']->get('obituarySearchCriterion');
    if(!$obituarySearchCriterion) {
        $obituarySearchCriterion = new ObituarySearchCriterion($domain);
    }
    else {
        $obituarySearchCriterion->setDomain($domain);
    }
    $home_places_obj = new ObituaryHomePlaces($app['db'], $obituarySearchCriterion);
    $home_places = $home_places_obj->getAll();

    $form = $app['form.factory']->createBuilder(new ObituarySearchForm($home_places, $domain), $obituarySearchCriterion)
//                                ->addValidator($app['validator'])
                                ->getForm();

    if($app['request']->getMethod() == 'POST') {
        // validation won't work for some reason for now
        // waiting for official FormExtension release

        // TODO: validate for domain_id in group domain ids

        if(!$app['request']->get('reset_x')) {
            $form->bindRequest($app['request']);
            // $ret = $app['validator']->validate($obituarySearchCriterion);
    //        if($form->isValid()) {
                $app['session']->set('obituarySearchCriterion', $obituarySearchCriterion);
    //        }
        }
        else {
            $obituarySearchCriterion = new ObituarySearchCriterion($domain);
            $app['session']->set('obituarySearchCriterion', $obituarySearchCriterion);
            return $app->redirect($app['request']->getPathInfo());
        }
    }
    $paginator = null;
    if($obituarySearchCriterion->notEmpty()) {
        $obitSearcher = new ObituarySearcher($app['db'], $obituarySearchCriterion, $app['request']->get('order'), $app['request']->get('dest'));
        $app['obitSearcher'] = $obitSearcher;
        $paginator = new Paginator($obitSearcher, $app['request']->get('page', 1), 10);
    }

    return $app['twig']->render('obituaries.twig', array(
        'form' => $form->createView(),
        'paginator' => $paginator
    ));
});

// twig helpers
function paginate(Paginator $paginator) {
    global $obituaries;

    return $obituaries['twig']->render('paginator.twig', array(
        'paginator' => $paginator
    ));
}

function order($link_text, $order_by) {
    global $obituaries;

    $page = $obituaries['request']->get('page');
    $order = $obituaries['obitSearcher']->get('order');
    $order_dest = $obituaries['obitSearcher']->get('order_dest');
    $class = '';

    if($order == $order_by) {
        if($order_dest=='desc') {
            $order_dest = '';
        }
        else {
            $order_dest = 'desc';
        }
        $class='class="obit-search-order-selected" ';
    }
    else {
        if($order_by=='death_date') {
            $order_dest = 'desc'; // default order for death date is descending
        }
        else {
            $order_dest = '';
        }
    }

    if($page) {
        $page="page=$page&";
    }

    if($order_dest) {
        $order_dest = '&dest='.$order_dest;
    }
    
    return "<a {$class}href=\"?{$page}order=$order_by$order_dest\">$link_text</a>";
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

function truncate($string, $symbols) {
    if(mb_strlen($string) > $symbols) {
        $string = mb_substr($string, 0, $symbols)."...";
    }
    return $string;
}

// stub to run without verse
if(!function_exists('leading_slash')) {
    function leading_slash($path) {
        return $path;
    }
}

return $app;
