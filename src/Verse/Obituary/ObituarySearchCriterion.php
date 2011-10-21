<?php
namespace Verse\Obituary;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints;

class ObituarySearchCriterion {
    public $domain,
           $domain_id,
           $text,
           $datefrom,
           $dateto,
           $homeplace;

    public function __construct($domain) {
        $this->domain = $domain;
        $this->datefrom = new \DateTime('-5 month');
        $this->dateto = new \DateTime();
        if($domain->isSingle()) {
            $this->domain_id = $domain->getDomainId();
        }
        else {
            $this->domain_id = null;
        }

    }

    public function setDomain($domain) {
        $this->domain = $domain;
    }

/*    public static function loadValidatorMetadata(ClassMetadata $metadata) {
        $metadata->addPropertyConstraint('text', new Constraints\MinLength(3));
//        $metadata->addPropertyConstraint('datefrom', new Constraints\Date());
    }
*/
    public function notEmpty() {
        return ($this->text || $this->datefrom || $this->dateto || $this->homeplace)?true:false;
    }

    // do not save domain object to session because it has DB connection
    public function __sleep() {
        return array('domain_id', 'text', 'datefrom', 'dateto', 'homeplace');
    }
}
