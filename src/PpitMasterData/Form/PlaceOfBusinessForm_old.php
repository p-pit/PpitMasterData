<?php
namespace CitMasterData\Form;

use Zend\Form\Form;

class PlaceOfBusinessForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('placeOfBusiness');
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
        	$this->add(array('name' => 'customer_id','type'  => 'hidden'));
        	$this->add(array('name' => 'identifier','type'  => 'text'));
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
        	

        	$this->add(array('name' => 'opening_date','type' => 'Zend\Form\Element\Date'));
			$this->add(array('name' => 'closing_date','type' => 'Zend\Form\Element\Date'));
 			$this->add(array('name' => 'place_of_business_id','type'  => 'hidden'));
        	 
        	$this->add(
        			array(
        					'name' => 'reception_contact_id',
        					'type' => 'Select',
        					'attributes' => array(
        							'id'    => 'reception_contact_id'
        					),
        					'options' => array(
        							'value_options' => NULL ,
        							'empty_option'  => '--- Please choose ---'
        					),
        			));
        	$this->add(
        			array(
        					'name' => 'bill_contact_id',
        					'type' => 'Select',
        					'attributes' => array(
        							'id'    => 'bill_contact_id'
        					),
        					'options' => array(
        							'value_options' => NULL ,
        							'empty_option'  => '--- Please choose ---'
        					),
        			));
        	$this->add(
        			array(
        					'name' => 'delivery_contact_id',
        					'type' => 'Select',
        					'attributes' => array(
        							'id'    => 'delivery_contact_id'
        					),
        					'options' => array(
        							'value_options' => NULL ,
        							'empty_option'  => '--- Please choose ---'
        					),
        			));

        	$this->add(array('name' => 'nb_people','type' => 'Zend\Form\Element\Number'));
        	$this->add(array('name' => 'surface','type' => 'Zend\Form\Element\Number'));
        	$this->add(array('name' => 'nb_floors','type' => 'Zend\Form\Element\Number'));
        	$this->add(array('name' => 'operational_hours','type'  => 'text'));
        	$this->add(array('name' => 'parking','type'  => 'text'));
        	$this->add(array('name' => 'lift','type'  => 'text'));
        	$this->add(array('name' => 'delivery_accessibility','type'  => 'text'));
        	$this->add(array('name' => 'security','type'  => 'text'));
        	$this->add(array('name' => 'logistic_constraints','type'  => 'text'));
        	$this->add(array('name' => 'submit','type'  => 'submit'));
        	 
        }
        }
        