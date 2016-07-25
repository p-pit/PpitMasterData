<?php
namespace CitMasterData\Form;

use Zend\Form\Form;

class PriceForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('price');
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
        	$this->add(array('name' => 'entity','type'  => 'text'));
        	
        	$this->add(
        			array(
        					'name' => 'entity_id',
        					'type' => 'Select',
        					'attributes' => array(
        							'id'    => 'entity_id'
        					),
        					'options' => array(
        							'value_options' => NULL ,
        							'empty_option'  => '--- Please choose ---'
        					),
        			));
        	
        	$this->add(
        			array(
        					'name' => 'category',
        					'type' => 'Select',
        					'attributes' => array(
        							'id'    => 'category'
        					),
        					'options' => array(
        							'value_options' => NULL ,
        							'empty_option'  => '--- Please choose ---'
        					),
        			));
        	 
        	$this->add(array('name' => 'price','type' => 'Zend\Form\Element\Number'));
        	$this->add(array('name' => 'is_including_vat','type' => 'Checkbox'));
        	$this->add(array('name' => 'vat_type','type' => 'Zend\Form\Element\Number'));
        	$this->add(array('name' => 'submit','type'  => 'submit'));
        	 
        }
        }
        