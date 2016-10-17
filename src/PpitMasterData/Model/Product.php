<?php
namespace PpitMasterData\Model;

use PpitContact\Model\Contract;
use PpitCore\Model\Context;
use PpitMasterData\Model\ProductCategory;
use PpitMasterData\Model\ProductOption;
use PpitMasterData\Model\ProductOptionMatrix;
use Zend\Db\Sql\Where;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Product implements InputFilterAwareInterface
{
    public $id;
    public $status;
    public $community_id;
    public $type;
    public $brand;
    public $reference;
    public $caption;
    public $description;
    public $is_available;
    public $property_1;
    public $property_2;
    public $property_3;
    public $property_4;
    public $property_5;
    public $property_6;
    public $property_7;
    public $property_8;
    public $property_9;
    public $property_10;
    public $variants;
    public $vat_1_id;
    public $vat_1_share;
    public $vat_2_id;
    public $vat_2_share;
    public $vat_3_id;
    public $vat_3_share;
    public $vat_4_id;
    public $vat_4_share;
    public $vat_5_id;
    public $vat_5_share;
    public $update_time;
    
    // Deprecated
    public $product_category_id;
    public $properties;
    public $criteria;
    public $prices;
    
    // Additional fields
    public $product_category;

    // Transient properties
    public $optionList;
    public $optionMatrix;
    
    protected $inputFilter;

    // Static fields
    private static $table;
    
    public function __construct()
    {
    	$config = Context::getCurrent()->getConfig();
    	$this->properties = array();
    	$this->prices = array();
    	foreach ($config['ppitMasterDataSettings']['priceCategories'] as $category => $caption) {
    		$this->prices[$category] = '';
    	}
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
    
    public function exchangeArray($data)
    {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->status = (isset($data['status'])) ? $data['status'] : null;
        $this->community_id = (isset($data['community_id'])) ? $data['community_id'] : null;
        $this->type = (isset($data['type'])) ? $data['type'] : null;
        $this->brand = (isset($data['brand'])) ? $data['brand'] : null;
        $this->reference = (isset($data['reference'])) ? $data['reference'] : null;
        $this->caption = (isset($data['caption'])) ? $data['caption'] : null;
        $this->description = (isset($data['description'])) ? $data['description'] : null;
        $this->is_available = (isset($data['is_available'])) ? $data['is_available'] : null;
        $this->property_1 = (isset($data['property_1'])) ? $data['property_1'] : null;
        $this->property_2 = (isset($data['property_2'])) ? $data['property_2'] : null;
        $this->property_3 = (isset($data['property_3'])) ? $data['property_3'] : null;
        $this->property_4 = (isset($data['property_4'])) ? $data['property_4'] : null;
        $this->property_5 = (isset($data['property_5'])) ? $data['property_5'] : null;
        $this->property_6 = (isset($data['property_6'])) ? $data['property_6'] : null;
        $this->property_7 = (isset($data['property_7'])) ? $data['property_7'] : null;
        $this->property_8 = (isset($data['property_8'])) ? $data['property_8'] : null;
        $this->property_9 = (isset($data['property_9'])) ? $data['property_9'] : null;
        $this->property_10 = (isset($data['property_10'])) ? $data['property_10'] : null;
        $this->variants = (isset($data['variants'])) ? json_decode($data['variants'], true) : array();
        $this->vat_1_id = (isset($data['vat_1_id'])) ? $data['vat_1_id'] : null;
        $this->vat_1_share = (isset($data['vat_1_share'])) ? $data['vat_1_share'] : null;
        $this->vat_2_id = (isset($data['vat_2_id'])) ? $data['vat_2_id'] : null;
        $this->vat_2_share = (isset($data['vat_2_share'])) ? $data['vat_2_share'] : null;
        $this->vat_3_id = (isset($data['vat_3_id'])) ? $data['vat_3_id'] : null;
        $this->vat_3_share = (isset($data['vat_3_share'])) ? $data['vat_3_share'] : null;
        $this->vat_4_id = (isset($data['vat_4_id'])) ? $data['vat_4_id'] : null;
        $this->vat_4_share = (isset($data['vat_4_share'])) ? $data['vat_4_share'] : null;
        $this->vat_5_id = (isset($data['vat_5_id'])) ? $data['vat_5_id'] : null;
        $this->vat_5_share = (isset($data['vat_5_share'])) ? $data['vat_5_share'] : null;
        $this->update_time = (isset($data['update_time'])) ? $data['update_time'] : array();

        // Deprecated
        $this->product_category_id = (isset($data['product_category_id'])) ? $data['product_category_id'] : null;
        $this->properties = (isset($data['properties'])) ? json_decode($data['properties'], true) : array();
        $this->criteria = (isset($data['criteria'])) ? $data['criteria'] : null;
        $this->prices = (isset($data['prices'])) ? json_decode($data['prices'], true) : array();
        
        // Additional fields
        $this->product_category = (isset($data['product_category'])) ? $data['product_category'] : null;
    }

    public function toArray()
    {
    	$data = array();
    	$data['id'] = (int) $this->id;
    	$data['status'] = $this->status;
    	$data['community_id'] = (int) $this->community_id;
    	$data['type'] = $this->type;
    	$data['brand'] = $this->brand;
    	$data['reference'] = $this->reference;
    	$data['caption'] = $this->caption;
    	$data['description'] = $this->description;
    	$data['is_available'] = $this->is_available;
    	$data['property_1'] = $this->property_1;
    	$data['property_2'] = $this->property_2;
    	$data['property_3'] = $this->property_3;
    	$data['property_4'] = $this->property_4;
    	$data['property_5'] = $this->property_5;
    	$data['property_6'] = $this->property_6;
    	$data['property_7'] = $this->property_7;
    	$data['property_8'] = $this->property_8;
    	$data['property_9'] = $this->property_9;
    	$data['property_10'] = $this->property_10;
	    $data['variants'] = json_encode($this->variants);
	    $data['vat_1_id'] = $this->vat_1_id;
	    $data['vat_1_share'] = $this->vat_1_share;
	    $data['vat_2_id'] = $this->vat_2_id;
	    $data['vat_2_share'] = $this->vat_2_share;
	    $data['vat_3_id'] = $this->vat_3_id;
	    $data['vat_3_share'] = $this->vat_3_share;
	    $data['vat_4_id'] = $this->vat_4_id;
	    $data['vat_4_share'] = $this->vat_4_share;
	    $data['vat_5_id'] = $this->vat_5_id;
	    $data['vat_5_share'] = $this->vat_5_share;
	     
	    // Deprecated
    	$data['product_category_id'] = (int) $this->product_category_id;
	    $data['properties'] = json_encode($this->properties);
	    $data['criteria'] = $this->criteria;
	    $data['prices'] = json_encode($this->prices);
	     
	    return $data;
    }

    public static function getList($type, $params, $major = 'caption', $dir = 'ASC', $mode = 'search')
    {
    	$context = Context::getCurrent();
    	$select = Product::getTable()->getSelect();
    	 
    	$where = new Where();
    
    	// Filter on type
    	if ($type) $where->equalTo('type', $type);

    	// Todo list vs search modes
    	if ($mode == 'todo') {
    		$todo = $context->getConfig('ppitProduct')['todo'];
    		foreach($todo as $role => $properties) {
    			if ($context->hasRole($role)) {
    				foreach($properties as $property => $predicate) {
    					if ($predicate['selector'] == 'equalTo') $where->equalTo($property, $predicate['value']);
    					elseif ($predicate['selector'] == 'in') $where->in($property, $predicate['value']);
    					elseif ($predicate['selector'] == 'deadline') $where->lessThanOrEqualTo($property, date('Y-m-d', strtotime(date('Y-m-d').'+ '.$predicate['value'].' days')));
    				}
    			}
    		}
    	}
    	else {
    			
    		// Set the filters
    		if (isset($params['brand'])) $where->like('brand', '%'.$params['brand'].'%');
    		if (isset($params['reference'])) $where->like('reference', '%'.$params['reference'].'%');
    		if (isset($params['caption'])) $where->like('caption', '%'.$params['caption'].'%');
    		if (isset($params['min_price'])) $where->greaterThanOrEqualTo('prices', $params['min_price']);
    		if (isset($params['max_price'])) $where->lessThanOrEqualTo('prices', $params['max_price']);
    
    		for ($i = 1; $i < 20; $i++) {
    			if (isset($params['property_'.$i])) $where->like('property_'.$i, '%'.$params['property_'.$i].'%');
    			if (isset($params['min_property_'.$i])) $where->greaterThanOrEqualTo('property_'.$i, $params['min_property_'.$i]);
    			if (isset($params['max_property_'.$i])) $where->lessThanOrEqualTo('property_'.$i, $params['max_property_'.$i]);
    		}
    	}
    
    	// Sort the list
    	$select->where($where)->order(array($major.' '.$dir, 'caption'));
    	$cursor = Product::getTable()->selectWith($select);
    	$products = array();
    	foreach ($cursor as $product) {
    		if (count($product->variants) > 0) $keep = false;
    		else $keep = true;
    		foreach ($product->variants as $variantId => $variantCriteria) {
    			$keepVariant = true;
    			foreach ($context->getConfig('ppitProduct'.(($type) ? '/'.$type : ''))['criteria'] as $criterion => $unused) {
    				if (array_key_exists($criterion, $params)) {
    					if ($variantCriteria[$criterion] != $params[$criterion]) $keepVariant = false;
    				}
    			}
    			if ($keepVariant) {
   					$keep = true;
    				break;
    			}
   			}
    		if ($keep) {
    			$product->properties = $product->toArray();
	    		$products[] = $product;
    		}
    	}
    
    	return $products;
    }

    public static function instanciate()
    {
		$product = new Product;
		return $product;
    }

    public static function getListForOrder($community_id, $productCategories)
    {
    	$context = Context::getCurrent();

    	// Construct the subquery of supplyers visible to this customer
    	$supplyers = Contract::getTable()->getSelect()
    		->columns(array('supplyer_community_id'))
    		->where(array('customer_community_id' => $context->getCommunityId(), 'function' => 'order'));
//    	$supplyers = Contract::getTable()->selectWith($select);
    	
		$select = Product::getTable()->getSelect();
		$where = new Where;
		$where->in('community_id', $supplyers);
		$where->equalTo('is_available', true);
		$select->where($where);
		$cursor = Product::getTable()->selectWith($select);
		$products = array();
		foreach ($cursor as $product) {
			if (array_key_exists($product->product_category_id, $productCategories)) {
				
				// Retrieve the available options for this product
				$select = ProductOption::getTable()->getSelect()->where(array('product_id' => $product->id))
					->where(array('is_available' => true))
					->order(array('caption'));
				$cursor2 = ProductOption::getTable()->selectWith($select);
				$product->optionList = array();
				foreach($cursor2 as $option) {
				
					$option->selected = false;
					$product->optionList[$option->id] = $option;
				}
	
				// Retrieve the option matrix
				$select = ProductOptionMatrix::getTable()->getSelect()
					->where(array('product_id' => $product->id));
				$cursor2 = ProductOptionMatrix::getTable()->selectWith($select);
				$product->optionMatrix = array();
				foreach($cursor2 as $cell) $product->optionMatrix[] = $cell;

				$products[] = $product;
			}
		}

		return $products;
    }

    public static function get($id)
    {
    	$product = Product::getTable()->get($id);
    	$product->product_category = ProductCategory::get($product->product_category_id);
		
		// Retrieve the available options for this product
		$select = ProductOption::getTable()->getSelect()->where(array('product_id' => $product->id))
			->where(array('is_available' => true))
			->order(array('caption'));
		$cursor2 = ProductOption::getTable()->selectWith($select);
		$product->optionList = array();
		foreach($cursor2 as $option) {
		
			$option->selected = false;
			$product->optionList[$option->id] = $option;
		}

		// Retrieve the option matrix
		$select = ProductOptionMatrix::getTable()->getSelect()
			->where(array('product_id' => $product->id));
		$cursor2 = ProductOptionMatrix::getTable()->selectWith($select);
		$product->optionMatrix = array();
		foreach($cursor2 as $cell) $product->optionMatrix[] = $cell;

    	return $product;
    }

    public function loadData($data)
    {
    	$config = Context::getCurrent()->getConfig();

    	// Retrieve the data from the request
    	$this->status = trim(strip_tags($data['status']));
    	$this->brand = trim(strip_tags($data['brand']));
    	$this->reference = trim(strip_tags($data['reference']));
    	$this->caption = trim(strip_tags($data['caption']));
    	$this->description = trim(strip_tags($data['description']));
    	$this->is_available = (int) $data['is_available'];
    
    	// Properties
    	$this->properties = array();
        foreach ($this->product_category->properties as $property => $definition) {
    		$this->properties[$property] = $data['property_'.$property];
    	}

    	// Prices
    	foreach ($config['ppitMasterDataSettings']['priceCategories'] as $category => $caption) {
    		$this->prices[$category] = (float) $data['price_'.$category];
    	}

    	// Check integrity
    	if ($this->brand == '' || strlen($this->brand) > 255) return 'Integrity';
    	if (strlen($this->reference) > 255) return 'Integrity';
    	if ($this->caption == '' || strlen($this->caption) > 255) return 'Integrity';
    	if (strlen($this->description) > 2047) return 'Integrity';
    	
    	// Check consistency
    	$select = Product::getTable()->getSelect()->where(array('brand' => $this->brand, 'reference' => $this->reference));
    	$cursor = Product::getTable()->selectWith($select);
    	if (count($cursor) > 0 && $cursor->current()->id != $this->id) return 'Duplicate';

    	return 'OK';
    }

    public function loadDataFromRequest($request) {
    	$config = Context::getCurrent()->getConfig();
    	$data = array();
    	$data['status'] = $request->getPost('status');
    	$data['brand'] = $request->getPost('brand');
    	$data['reference'] = $request->getPost('reference');
    	$data['caption'] = $request->getPost('caption');
    	$data['description'] = $request->getPost('description');
    	$data['is_available'] = $request->getPost('is_available');

    	// Properties
        foreach ($this->product_category->properties as $property => $definition) {
    		$data['property_'.$property] = $request->getPost('property_'.$property);
    	}

    	// Prices
    	foreach ($config['ppitMasterDataSettings']['priceCategories'] as $category => $caption) {
    		$data['price_'.$category] = $request->getPost('price_'.$category);
    	}
    	$return = $this->loadData($data);
    	if ($return == 'Integrity') throw new \Exception('View error');
		return $return;
    }

    public function add()
    {
		Product::getTable()->save($this);
		return 'OK';
    }

    public function update($update_time)
    {
		$product = Product::getTable()->get($this->id);
		if ($product->update_time > $update_time) return 'Isolation';
		Product::getTable()->save($this);
		return 'OK';
    }

    public function isDeletable()
    {
    	$config = Context::getCurrent()->getConfig();
    	foreach($config['ppitMasterDataDependencies'] as $dependency) {
    		if ($dependency->isUsed($this)) return false;
    	}
    	return true;
    }

    public function delete($update_time)
    {
    	$product = Product::getTable()->get($this->id);
    	if ($product->update_time > $update_time) return 'Isolation';
    	
    	// Delete options related to the product
    	ProductOption::getTable()->multipleDelete(array('product_id' => $this->product_id));
    	
    	// Delete the product
    	Product::getTable()->delete($this->id);

    	return 'OK';
    }

    // Add content to this method:
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
    	if (!Product::$table) {
    		$sm = Context::getCurrent()->getServiceManager();
    		Product::$table = $sm->get('PpitMasterData\Model\ProductTable');
    	}
    	return Product::$table;
    }
}
