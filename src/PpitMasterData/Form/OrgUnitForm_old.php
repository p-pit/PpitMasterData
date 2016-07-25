<?php
namespace CitMasterData\Form;

use Zend\Form\Form;

class OrgUnitForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('orgUnit');
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
        	
        	$this->add(
        			array(
        					'name' => 'type',
        					'type' => 'Select',
        					'attributes' => array(
        							'id'    => 'type'
        					),
        					'options' => array(
        							'value_options' =>NULL ,
        							'empty_option'  => '--- Please choose ---'
        					),
        			));
        	 
        	
        	$this->add(array('name' => 'level','type' => 'Zend\Form\Element\Number'));
        	$this->add(array('name' => 'parent_id','type'  => 'hidden'));
        	$this->add(array('name' => 'place_of_business_id','type'  => 'hidden'));
        	$this->add(array('name' => 'identifier','type'  => 'text'));
        	$this->add(array('name' => 'caption','type'  => 'text'));
        	$this->add(array('name' => 'description','type'  => 'textarea'));
        	$this->add(array('name' => 'resp_post_id','type'  => 'hidden'));
        	$this->add(array('name' => 'submit','type'  => 'submit'	));
        	 
        }
        }
        