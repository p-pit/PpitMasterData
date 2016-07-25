<?php
namespace CitMasterData\Form;

use Zend\Form\Form;

class ProductForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('product');
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
    	
    	
    	$this->add(array('name' => 'brand','type'  => 'text'));
    	$this->add(array('name' => 'model','type'  => 'text'));
    	$this->add(array('name' => 'description','type'  => 'textarea'));
    	$this->add(array('name' => 'default_quantity','type' => 'Zend\Form\Element\Number'));
    	$this->add(array('name' => 'catalogue_page','type' => 'Zend\Form\Element\Number'));
    	$this->add(array('name' => 'e_commerce_link','type'  => 'text'));
    	$this->add(array('name' => 'blog_link','type'  => 'text'));
		$this->add(array('name' => 'submit','type'  => 'submit'));
    	
    }
}
        