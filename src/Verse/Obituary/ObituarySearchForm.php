<?php
namespace Verse\Obituary;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ObituarySearchForm extends AbstractType {
    protected
        $home_places,
        $domain;

    public function __construct($home_places, $domain) {
        $this->home_places = $home_places;
        $this->domain = $domain;
    }

    public function buildForm(FormBuilder $builder, array $options) {
        $builder->add('text', 'text', array('required'=>false));
        $builder->add('datefrom', 'date', array('widget'=>'single_text', 'format'=>'MM/dd/y'));
        $builder->add('dateto', 'date', array('widget'=>'single_text', 'format'=>'MM/dd/y'));
//        $builder->add('datefrom', 'date', array('years'=>range(2000, date('Y'))));
//        $builder->add('dateto', 'date', array('years'=>range(2000, date('Y'))));
        $builder->add('homeplace', 'choice', array('choices' => $this->home_places, 'required'=>false));
        if(!$this->domain->isSingle()) {
            $builder->add('domain_id', 'choice', array('choices' => $this->domain->getGroupDomains(), 'required'=>false));
        }
    }

    public function getName() {
        return "obit_search";
    }
}
