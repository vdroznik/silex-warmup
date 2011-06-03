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
        return $this->paginee->getItems($this->page, $this->records_per_page);
    }

    public function getTotalPages() {
        $pages = 1;
        if($this->records_per_page) {
            $pages = ceil($this->paginee->getTotalRecords() / $this->records_per_page);
        }
        return $pages;
    }
}
