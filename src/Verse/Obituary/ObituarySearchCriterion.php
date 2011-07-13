<?php
namespace Verse\Obituary;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints;

class ObituarySearchCriterion {
    public $domain_id,
           $text,
           $datefrom,
           $dateto,
           $homeplace;

    public function __construct($domain_id) {
        $this->domain_id = $domain_id;
        $this->datefrom = new \DateTime('2000-01-01');
        $this->dateto = new \DateTime();
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata) {
        $metadata->addPropertyConstraint('text', new Constraints\MinLength(3));
//        $metadata->addPropertyConstraint('datefrom', new Constraints\Date());
    }

    public function notEmpty() {
        return strlen($this->text);
    }
}
