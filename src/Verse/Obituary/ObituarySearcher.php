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
        $cond = $this->buildWhereClause();
        $query = 'SELECT first_name, middle_name, last_name, death_date, home_place, image FROM plg_obituary WHERE '.$cond['where'];
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
        $items = $this->db->fetchAll($query, $cond['params']);
        return $items;
    }

    public function getPage() {

    }

    public function getTotalRecords() {
        $cond = $this->buildWhereClause();
        $query = 'SELECT count(*) FROM plg_obituary WHERE '.$cond['where'];
        return $this->db->fetchColumn($query, $cond['params']);
    }

    protected function buildWhereClause() {
        $where = 'domain_id=:domain_id AND (first_name LIKE :name OR middle_name LIKE :name OR last_name LIKE :name)
                    AND death_date>:death_date_from AND death_date<:death_date_to';
        $params = array('domain_id'=>$this->criterion->domain_id,
                        'name'=>'%'.$this->criterion->text.'%',
                        'death_date_from'=>$this->criterion->datefrom->format('Y-m-d'),
                        'death_date_to'=>$this->criterion->dateto->format('Y-m-d')
                        );
        if($this->criterion->homeplace) {
            $where.=" AND home_place=:home_place";
            $params['home_place'] = $this->criterion->homeplace;
        }
        return array('where'=>$where, 'params'=>$params);
    }
}
