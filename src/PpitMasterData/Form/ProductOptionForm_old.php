<?php
namespace CitMasterData\Form;

use Zend\Form\Form;

class ProductOptionForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('productOption');
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        }
        
        public function addElements($vcards)
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
        	$this->add(array('name' => 'reference','type'  => 'hidden'));
        	$this->add(array('name' => 'is_on_sale','type' => 'Checkbox'));
        	$this->add(array('name' => 'caption','type'  => 'hidden'));
        	$this->add(array('name' => 'identifier','type'  => 'text'	));
        	$this->add(array('name' => 'name','type'  => 'text'));
        	 
        	$this->add(
        			array(
        					'name' => 'admin_region',
        					'type' => 'Select',
        					'attributes' => array(
        							'id'    => 'admin_region'
        					),
        					'options' => array(
        							'value_options' => NULL ,
        							'empty_option'  => '--- Please choose ---'
        					),
        			));
        	 
        	$this->add(array('name' => 'division_id','type'  => 'hidden'));
        	$this->add(array('name' => 'place_of_business_id','type'  => 'hidden',));
        	 
        	$this->add(
        			array(
        					'name' => 'type',
        					'type' => 'Select',
        					'attributes' => array(
        							'id'    => 'type'
        					),
        					'options' => array(
        							'value_options' => NULL ,
        							'empty_option'  => '--- Please choose ---'
        					),
        			));
        	 
        	$this->add(array('name' => 'submit','type'  => 'submit',));
        	 
        }
        }
        