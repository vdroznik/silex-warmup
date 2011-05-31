<?php
namespace Verse\Obituary;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints;

class ObituarySearchCriterion {
    public $text,
           $datefrom,
           $dateto;

    public static function loadValidatorMetadata(ClassMetadata $metadata) {
        $metadata->addPropertyConstraint('text', new Constraints\MinLength(3));
//        $metadata->addPropertyConstraint('datefrom', new Constraints\Date());
    }
}
