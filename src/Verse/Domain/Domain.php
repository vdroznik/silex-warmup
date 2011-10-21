<?php
namespace Verse\Domain;
 
class Domain {
    protected
        $db = null,
        $domain_id = null,
        $group_domain_ids = null;

    function __construct($db, $domain_id) {
        $this->db = $db;
        $this->domain_id = $domain_id;
    }

    public function getDomainId() {
        return $this->domain_id;
    }

    public function getDomainName() {
        
    }

    public function getGroupDomainIds() {
        if(!$this->group_domain_ids) {
            $this->group_domain_ids = $this->db->executeQuery('SELECT domain_id FROM sms_domain_link WHERE group_id=(SELECT group_id FROM sms_domain_link WHERE domain_id=:domain_id)',
                                                    array('domain_id'=>$this->domain_id))
                                               ->fetchAll(\PDO::FETCH_COLUMN);
            if(!$this->group_domain_ids) {
                $this->group_domain_ids = array($this->domain_id);
            }
        }
        return $this->group_domain_ids;
    }

    public function getGroupDomains() {
        $ret = $this->db->executeQuery('SELECT d.domain_id, title FROM sms_domain_link l
                                 INNER JOIN cms_site d USING(domain_id)
                                 WHERE group_id=(SELECT group_id FROM sms_domain_link WHERE domain_id=:domain_id)',
                                   array('domain_id'=>$this->domain_id))
                                 ->fetchAll(\PDO::FETCH_NAMED);
        $domains = array();
        if($ret) {
            foreach($ret as $domain) {
                $domains[$domain['domain_id']] = $domain['title'];
            }
        }
        return $domains;
    }

    public function isSingle() {
        return count($this->getGroupDomainIds()) == 1;
    }

    public function isSharedObits() {
        return $this->db->fetchOne("SELECT is_shared_obits FROM sms_domain_link_group lg JOIN sms_domain_link l on l.group_id=lg.id WHERE domain_id=:domain_id", array('domain_id'=>$this->domain_id));
    }
}
