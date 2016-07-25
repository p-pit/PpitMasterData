<?php
namespace CitMasterData\Form;

use Zend\Form\Form;

class ProductOptionMatrixForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('productOptionMatrix');
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
        	$this->add(array('name' => 'product_id','type'  => 'hidden'));
        	$this->add(array('name' => 'row_option_id','type' => 'Zend\Form\Element\Number'));
        	$this->add(array('name' => 'col_option_id','type' => 'Zend\Form\Element\Number'));
        	$this->add(array('name' => 'constraint','type' => 'Zend\Form\Element\Number'));
        	$this->add(array('name' => 'submit','type'  => 'submit'));
        	 
        }
        }
        