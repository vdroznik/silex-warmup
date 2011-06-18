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

    public function getCurrent() {
        return $this->page;
    }

    public function getResults() {
        static $results;
        if(!$results) {
            $results = $this->paginee->getTotalRecords();
        }
        return $results;
    }

    public function getItems() {
        return $this->paginee->getItems($this->page, $this->records_per_page);
    }

    public function getTotalPages() {
        static $total;
        if(!$total) {
            $pages = 1;
            if($this->records_per_page) {
                $pages = ceil($this->paginee->getTotalRecords() / $this->records_per_page);
            }
            $total = $pages;
        }
        return $total;
    }
}
