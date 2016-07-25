<?php
namespace CitMasterData\Form;

use Zend\Form\Form;

class ProductCategoryForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('product_category');
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        
        $this->add(array(
        		'name' => 'caption',
        		'attributes' => array(
					'id' => 'caption',
        			'type'  => 'text',
        			'size'  => '255',
        		),
        		'options' => array(
        				'label' => 'Caption',
        		),
        ));
	     
        $vatRates = array();
        foreach (\CitMasterData\Parameters\Parameters::$vatRates as $caption => $value) $vatRates[$caption] = $caption;
	    $this->add(array(
	    		'name' => 'vat_rate',
	    		'type'  => 'Select',
	    		'attributes' => array(
	    				'id'    => 'vat_rate'
	    		),
	    		'options' => array(
	    				'label' => 'VAT rate',
	    				'value_options' => $vatRates,
	    				'empty_option'  => '--- Please choose ---'
	    		),
	    ));
	    
	    // submit button
        $this->add(array(
            'name' => 'submit',
            'type'  => 'submit',
        	'attributes' => array(
                'value' => 'Submit',
                'id' => 'submit',
            ),
        ));
        // Form protection
        $this->add(
            array(
                'name' => 'csrf',
                'type' => 'Csrf',
                'options' => array(
                    'csrf_options' => array(
                        'timeout' => 600
                    )
                )
            )
        );
        // Hide fields
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'site_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
    }
}
