<?php
namespace PpitMasterData\Model;

use PpitCore\Model\Context;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Option implements InputFilterAwareInterface
{
    public $id;
    public $instance_id;
    public $caption;
    public $is_on_sale;
    public $default_quantity;
    protected $inputFilter;

    // Static fields
    private static $table;
    
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function exchangeArray($data)
    {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->instance_id = (isset($data['instance_id'])) ? $data['instance_id'] : null;
        $this->caption = (isset($data['caption'])) ? $data['caption'] : null;
        $this->is_on_sale = (isset($data['is_on_sale'])) ? $data['is_on_sale'] : null;
      	$this->default_quantity = (isset($data['default_quantity'])) ? $data['default_quantity'] : null;
    }

    // Add content to this method:
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $inputFilter->add($factory->createInput(array(
            		'name'     => 'csrf',
            		'required' => false,
            )));
            
            $inputFilter->add($factory->createInput(array(
            		'name'     => 'caption',
            		'required' => false,
            		'filters'  => array(
            				array('name' => 'StripTags'),
            				array('name' => 'StringTrim'),
            		),
            		'validators' => array(
            				array(
            						'name'    => 'StringLength',
            						'options' => array(
            								'encoding' => 'UTF-8',
            								'min'      => 1,
            								'max'      => 255,
            						),
            				),
            		),
            )));
            
            
            
            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
    
    public function toArray()
    {
    	$data = array();
    	$data['id'] = (int) $this->id;
    	$data['caption'] =  $this->caption;
    	$data['is_on_sale'] = (int) $this->is_on_sale;
    	$data['default_quantity'] = (int) $this->default_quantity;
    
    	return $data;
    }

    public static function getTable()
    {
    	if (!Option::$table) {
    		$sm = Context::getCurrent()->getServiceManager();
    		Option::$table = $sm->get('PpitMasterData\Model\OptionTable');
    	}
    	return Option::$table;
    }
}
