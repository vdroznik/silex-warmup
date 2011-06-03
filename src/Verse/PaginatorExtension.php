<?php
namespace Verse;

use Verse\Paginator;

class PaginatorExtension extends \Twig_Extension {

    public function paginate(Paginator $paginator) {

        return "1 2 3";
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'paginator';
    }
}
