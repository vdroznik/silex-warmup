<?php
namespace Verse\Obituary;

use Verse\Paginable;
use Doctrine\DBAL\Connection as DoctrineConnection;

class ObituarySearcher implements Paginable {
    protected $db,
              $criterion,
              $order,
              $order_dest;

    public function __construct(DoctrineConnection $db, ObituarySearchCriterion $criterion, $order= null, $order_dest = null) {
        $this->db = $db;
        $this->criterion = $criterion;
        $this->order = $order;
        $this->order_dest = $order_dest;
    }

    public function getItems($page, $rpp = null) {
        $query = 'SELECT first_name, middle_name, last_name, death_date, home_place, image FROM plg_obituary
                    WHERE domain_id=:domain_id AND (first_name LIKE :name OR middle_name LIKE :name OR last_name LIKE :name)';
        if($this->criterion->homeplace) {
            $query.=" AND home_place=:home_place";
        }
        if($this->order) {
            $query.=" ORDER BY ".$this->db->quoteIdentifier($this->order);
            if($this->order_dest=='desc') {
                $query.=" DESC";
            }
        }
        if($rpp) {
            $limit_clause = " LIMIT ".($page-1)*$rpp.", $rpp";
            $query.=$limit_clause;
        }
        $items = $this->db->fetchAll($query, array('domain_id'=>$this->criterion->domain_id,
                                                   'name'=>'%'.$this->criterion->text.'%',
                                                   'home_place'=>$this->criterion->homeplace));
        return $items;
    }

    public function getPage() {

    }

    public function getTotalRecords() {
        $query = 'SELECT count(*) FROM plg_obituary
                    WHERE domain_id=:domain_id AND (first_name LIKE :name OR middle_name LIKE :name OR last_name LIKE :name)';
        if($this->criterion->homeplace) {
            $query.=" AND home_place=:home_place";
        }
        return $this->db->fetchColumn($query, array('domain_id'=>$this->criterion->domain_id,
                                                    'name'=>'%'.$this->criterion->text.'%',
                                                    'home_place'=>$this->criterion->homeplace));
    }
    
}
