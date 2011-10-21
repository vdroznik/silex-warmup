<?php
namespace Verse\Obituary;

use Verse\Obituary\ObituarySearchCriterion;
 
class ObituaryHomePlaces {
    protected
        $db = null,
        /**
         * @var \Verse\Domain\Domain $domain
         */
        $searchCriterion = null;

    function __construct($db, ObituarySearchCriterion $searchCriterion) {
        $this->db = $db;
        $this->searchCriterion = $searchCriterion;
    }

    public function getAll() {
        if($this->searchCriterion->domain_id) {
            $ret = $this->db->executeQuery('SELECT DISTINCT home_place FROM plg_obituary WHERE domain_id=:domain_id ORDER BY home_place', array('domain_id' => $this->searchCriterion->domain_id))
                            ->fetchAll(\PDO::FETCH_COLUMN);
        }
        else {
            $ret = $this->db->executeQuery('SELECT DISTINCT home_place FROM plg_obituary WHERE domain_id IN('.implode(",", $this->searchCriterion->domain->getGroupDomainIds()).') ORDER BY home_place')
                            ->fetchAll(\PDO::FETCH_COLUMN);
        }
        $home_places = array();
        foreach($ret as $home_place) {
            $home_place = trim($home_place);
            if(strlen($home_place)>2) {
                $home_places[$home_place] = $home_place;
            }
        }
        return $home_places;
    }
}
