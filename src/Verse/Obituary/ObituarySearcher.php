<?php
namespace Verse\Obituary;

use Verse\Paginable;
use Doctrine\DBAL\Connection as DoctrineConnection;

class ObituarySearcher implements Paginable {
    protected $db,
              $criterion,
              $page;

    public function __construct(DoctrineConnection $db, ObituarySearchCriterion $criterion, $page = 1) {
        $this->db = $db;
        $this->criterion = $criterion;
        $this->page = $page;
    }

    public function getItems() {
        
    }

    public function getPage() {

    }

    public function getTotalPages() {
        
    }
    
}
