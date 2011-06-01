<?php
namespace Verse;

class Paginator {
    protected $paginee,
              $page,
              $records_per_page;

    public function __construct(Paginable $paginee, $page = 1, $records_per_page = null) {
        $this->paginee = $paginee;
        $this->page = $page;
        $this->records_per_page = $records_per_page;
    }

    public function getItems() {
        return $this->paginee->getItems();
    }
}
