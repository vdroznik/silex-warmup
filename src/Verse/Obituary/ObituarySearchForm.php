<?php
namespace Verse\Obituary;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ObituarySearchForm extends AbstractType {
    protected $home_places;

    public function __construct($home_places) {
        $this->home_places = $home_places;
    }

    public function buildForm(FormBuilder $builder, array $options) {
        $builder->add('text', 'text');
        $builder->add('datefrom', 'date', array('years'=>range(2000, date('Y'))));
        $builder->add('dateto', 'date', array('years'=>range(2000, date('Y'))));
        $builder->add('homeplace', 'choice', array('choices' => $this->home_places, 'required'=>false));
    }
}
