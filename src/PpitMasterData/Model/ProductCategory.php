<?php
namespace PpitMasterData\Model;

use PpitCore\Model\Context;
use PpitCore\Model\Generic;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class ProductCategory implements InputFilterAwareInterface
{
    public $id;
    public $caption;
    public $properties;
    public $reduced_vat_share;
    public $intermediary_vat_share;
    public $standard_vat_share;
    public $update_time;
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
        $this->caption = (isset($data['caption'])) ? $data['caption'] : null;
        $this->properties = (isset($data['properties'])) ? json_decode($data['properties'], true) : array();
        $this->reduced_vat_share = (isset($data['reduced_vat_share'])) ? $data['reduced_vat_share'] : null;
        $this->intermediary_vat_share = (isset($data['intermediary_vat_share'])) ? $data['intermediary_vat_share'] : null;
        $this->standard_vat_share = (isset($data['standard_vat_share'])) ? $data['standard_vat_share'] : null;
        $this->update_time = (isset($data['update_time'])) ? $data['update_time'] : null;
    }

    public function toArray()
    {
    	$data = array();
    	$data['id'] = (int) $this->id;
    	$data['caption'] = $this->caption;
	    $data['properties'] = json_encode($this->properties);
    	$data['reduced_vat_share'] = (float) $this->reduced_vat_share;
   		$data['intermediary_vat_share'] = (float) $this->intermediary_vat_share;
   		$data['standard_vat_share'] = (float) $this->standard_vat_share;
   		return $data;
    }

    public static function instanciate()
    {
    	$config = Context::getCurrent()->getConfig();
    	$productCategory = new ProductCategory;
    	$productCategory->properties = $config['ppitMasterDataSettings']['defaultProductProperties'];
    	return $productCategory;
    }
    
    public static function get($id)
    {
    	return ProductCategory::getTable()->get($id);
    }
    
    public function loadData($data)
    {
    	$config = Context::getCurrent()->getConfig();
    
    	// Retrieve the data from the request
    	$this->caption = trim(strip_tags($data['caption']));

    	// Check integrity
    	if ($this->caption == '' || strlen($this->caption) > 255) return 'Integrity';
    	 
    	// Check consistency
    	$select = ProductCategory::getTable()->getSelect()->where(array('caption' => $this->caption));
    	$cursor = ProductCategory::getTable()->selectWith($select);
    	if (count($cursor) > 0 && $cursor->current()->id != $this->id) return 'Duplicate';
    
    	return 'OK';
    }
    
    public function loadDataFromRequest($request) {
    	$config = Context::getCurrent()->getConfig();
    	$data = array();
    	$data['caption'] = $request->getPost('caption');
    	$return = $this->loadData($data);
    	if ($return == 'Integrity') throw new \Exception('View error');
    	return $return;
    }
    
    public function add()
    {
    	ProductCategory::getTable()->save($this);
    	return 'OK';
    }
    
    public function update($update_time)
    {
    	$productCategory = ProductCategory::getTable()->get($this->id);
    	if ($productCategory->update_time > $update_time) return 'Isolation';
    	ProductCategory::getTable()->save($this);
    	return 'OK';
    }

    public function isDeletable()
    {
    	if (Generic::getTable()->cardinality('md_product', array('product_category_id' => $this->id)) > 0) return false;
    	return true;
    }

    public function delete($update_time)
    {
    	$productCategory = ProductCategory::getTable()->get($this->id);
    	if ($productCategory->update_time > $update_time) return 'Isolation';
    	ProductCategory::getTable()->delete($this->id);
    	return 'OK';
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        throw new \Exception("Not used");
    }

    public static function getTable()
    {
    	if (!ProductCategory::$table) {
    		$sm = Context::getCurrent()->getServiceManager();
    		ProductCategory::$table = $sm->get('PpitMasterData\Model\ProductCategoryTable');
    	}
    	return ProductCategory::$table;
    }
}
