<?php
namespace PpitMasterData\Model;

use PpitCore\Model\Context;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class ProductOptionMatrix implements InputFilterAwareInterface
{
    public $id;
    public $product_id;
    public $row_option_id;
    public $col_option_id;
    public $constraint;
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
        $this->product_id = (isset($data['product_id'])) ? $data['product_id'] : null;
        $this->row_option_id = (isset($data['row_option_id'])) ? $data['row_option_id'] : null;
        $this->col_option_id = (isset($data['col_option_id'])) ? $data['col_option_id'] : null;
        $this->constraint = (isset($data['constraint'])) ? $data['constraint'] : null;
    }

    public function toArray()
    {
    	$data = array();
    	$data['id'] = (int) $this->id;
    	$data['product_id'] = (int) $this->product_id;
    	$data['row_option_id'] = (int) $this->row_option_id;
    	$data['col_option_id'] = (int) $this->col_option_id;
    	$data['constraint'] = (int) $this->constraint;
    
    	return $data;
    }
    
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
		return $this->inputFilter;
    }

    public static function getTable()
    {
    	if (!ProductOptionMatrix::$table) {
    		$sm = Context::getCurrent()->getServiceManager();
    		ProductOptionMatrix::$table = $sm->get('PpitMasterData\Model\ProductOptionMatrixTable');
    	}
    	return ProductOptionMatrix::$table;
    }
}
