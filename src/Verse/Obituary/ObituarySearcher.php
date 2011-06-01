<?php
namespace Verse\Obituary;

use Verse\Paginable;
use Doctrine\DBAL\Connection as DoctrineConnection;

class ObituarySearcher implements Paginable {
    protected $db,
              $criterion;

    public function __construct(DoctrineConnection $db, ObituarySearchCriterion $criterion) {
        $this->db = $db;
        $this->criterion = $criterion;
    }

    public function getItems() {
        $items = $this->db->fetchAll('SELECT first_name, middle_name, last_name FROM plg_obituary
            WHERE first_name LIKE :name OR middle_name LIKE :name OR last_name LIKE :name', array('name'=>$this->criterion->text));
        return $items;
    }

    public function getPage() {

    }

    public function getTotalPages() {
        
    }
    
}
