<?php
namespace CitMasterData\Form;

use Zend\Form\Form;

class OptionForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('option');
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        }
        
        public function addElements()
        {
        	$this->add(array(
        			'name' => 'csrf',
        			'type' => 'Csrf',
        			'options' => array(
        					'csrf_options' => array(
        							'timeout' => 600
        					)
        			)
        	));
        	 
        	$this->add(array('name' => 'id','type'  => 'hidden'));        
        	$this->add(array('name' => 'caption','type'  => 'text'));    	 
      		$this->add(array('name' => 'is_on_sale','type' => 'Checkbox' ));
			$this->add(array('name' => 'default_quantity','type' => 'Zend\Form\Element\Number'));
			$this->add(array('name' => 'submit','type'  => 'submit'));
        	 
        }
        }
        