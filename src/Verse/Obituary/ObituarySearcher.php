<?php
namespace Verse\Obituary;

use Verse\Paginable;
use Doctrine\DBAL\Connection as DoctrineConnection;

class ObituarySearcher implements Paginable {
    protected $db,
              $criterion,
              $order,
              $order_dest;

    public function __construct(DoctrineConnection $db, ObituarySearchCriterion $criterion, $order = null, $order_dest = null) {
        $this->db = $db;
        $this->criterion = $criterion;
        if(!$order) {
            $order = 'death_date';
            $order_dest = 'desc';
        }
        $this->order = $order;
        $this->order_dest = $order_dest;
    }

    public function getItems($page, $rpp = null) {
        if($this->criterion->domain->isSingle()) {
            // single domain
            $query = 'SELECT o.obituary_id, o.domain_id, first_name, middle_name, last_name, death_date, home_place, image, obit_text, s.title FROM plg_obituary o INNER JOIN cms_site s USING(domain_id) WHERE '.$this->buildWhere();
        }
        else {
            // linked domains
            $query = 'SELECT o.obituary_id, o.domain_id, d.domain_name, first_name, middle_name, last_name, death_date, home_place, image, obit_text, s.title FROM plg_obituary o INNER JOIN cms_site s USING(domain_id) INNER JOIN sms_domain d USING(domain_id) WHERE '.$this->buildWhere();
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
        $items = $this->db->fetchAll($query, $this->buildParamsArray());
        if(!$this->criterion->domain->isSingle()) {
            // multiple domains mode
            foreach($items as &$item) {
                if($item['image'] && $item['domain_id'] != $this->criterion->domain->getDomainId()) {
                    $item['image'] = $item['domain_name'].'/'.$item['image'];
                }
            }
        }
        return $items;
    }

    public function get($param) {
        if(isset($this->$param)) {
            return $this->$param;
        }
        else {
            return null;
        }
    }

    public function getTotalRecords() {
        $query = 'SELECT count(*) FROM plg_obituary WHERE '.$this->buildWhere();
        return $this->db->fetchColumn($query, $this->buildParamsArray());
    }

    protected function buildWhere() {
        if($this->criterion->domain->isSingle()) {
            // single domain mode
            $where = 'domain_id=:domain_id';
        } else {
            // linked domains mode
            $where = 'domain_id IN ('.implode(',', $this->criterion->domain->getGroupDomainIds()).')';
        }
        if($this->criterion->text) {
            $where.=' AND (first_name LIKE :name OR middle_name LIKE :name OR last_name LIKE :name)';
        }
        if($this->criterion->homeplace) {
            $where.=' AND home_place=:homeplace';
        }
        if($this->criterion->datefrom) {
            $where.=' AND death_date>=:datefrom';
        }
        if($this->criterion->dateto) {
            $where.=' AND death_date<=:dateto';
        }
        return $where;
    }

    protected function buildParamsArray() {
        $params = array();

        if($this->criterion->domain_id) {
            $params['domain_id'] = $this->criterion->domain_id;
        }
        if($this->criterion->text) {
            $params['name'] = '%'.$this->criterion->text.'%';
        }
        if($this->criterion->homeplace) {
            $params['homeplace'] = $this->criterion->homeplace;
        }
        if($this->criterion->datefrom) {
            $params['datefrom'] = $this->criterion->datefrom->format('Y-m-d');
        }
        if($this->criterion->dateto) {
            $params['dateto'] = $this->criterion->dateto->format('Y-m-d');
        }

        return $params;
    }
}
