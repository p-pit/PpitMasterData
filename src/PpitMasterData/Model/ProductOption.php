<?php
namespace PpitMasterData\Model;

use PpitCore\Model\Context;
use Zend\Db\Sql\Where;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class ProductOption implements InputFilterAwareInterface
{
    public $id;
    public $status;
    public $product_id;
    public $reference;
    public $caption;
    public $description;
    public $is_available;
    public $variants;
    public $vat_id;
    public $update_time;

    // Deprecated
    public $prices;
    
    // Additional properties from joined entities
    public $price;
    
    // Transient properties
    public $product;
    public $properties;
    
    protected $inputFilter;

    // Static fields
    private static $table;

    public function __construct()
    {
    	$config = Context::getCurrent()->getConfig();
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
        $this->product_id = (isset($data['product_id'])) ? $data['product_id'] : null;
        $this->reference = (isset($data['reference'])) ? $data['reference'] : null;
        $this->caption = (isset($data['caption'])) ? $data['caption'] : null;
        $this->description = (isset($data['description'])) ? $data['description'] : null;
        $this->is_available = (isset($data['is_available'])) ? $data['is_available'] : null;
        $this->variants = (isset($data['variants'])) ? json_decode($data['variants'], true) : array();
        $this->vat_id = (isset($data['vat_id'])) ? $data['vat_id'] : null;
        $this->update_time = (isset($data['update_time'])) ? json_decode($data['update_time']) : array();

        // Deprecated
        $this->prices = (isset($data['prices'])) ? json_decode($data['prices'], true) : array();
        
        // Addistional fields
        $this->price = (isset($data['price'])) ? $data['price'] : null;
    }

    public function toArray()
    {
    	$data = array();
    	$data['id'] = (int) $this->id;
    	$data['status'] = $this->status;
    	$data['product_id'] = (int) $this->product_id;
    	$data['reference'] = $this->reference;
    	$data['caption'] = $this->caption;
    	$data['description'] = $this->description;
    	$data['is_available'] = (int) $this->is_available;
    	$data['variants'] = json_encode($this->variants);
    	$data['vat_id'] = $this->vat_id;
    	 
    	// Deprecated
    	$data['prices'] = json_encode($this->prices);

    	return $data;
    }

    public static function instanciate($product_id = null)
    {
    	$productOption = new ProductOption;
    	$productOption->product_id = $product_id;
    	if ($product_id) $productOption->product = Product::get($product_id);
    	return $productOption;
    }

    public static function getList($params, $major = 'reference', $dir = 'ASC', $mode = 'search')
    {
    	$context = Context::getCurrent();
    	$select = ProductOption::getTable()->getSelect();
    	 
    	$where = new Where();
    	$where->notEqualTo('md_product_option.status', 'deleted');
    
    	// Todo list vs search modes
    	if ($mode == 'todo') {
/*    
    		$todo = $context->getConfig('commitment'.(($type) ? '/'.$type : ''))['todo'];
    		foreach($todo as $role => $properties) {
    			if ($context->hasRole($role)) {
    				foreach($properties as $property => $predicate) {
    					if ($predicate['selector'] == 'equalTo') $where->equalTo('commitment.'.$property, $predicate['value']);
    					elseif ($predicate['selector'] == 'in') $where->in('commitment.'.$property, $predicate['value']);
    					elseif ($predicate['selector'] == 'deadline') $where->lessThanOrEqualTo('commitment.'.$property, date('Y-m-d', strtotime(date('Y-m-d').'+ '.$predicate['value'].' days')));
    				}
    			}
    		}*/
    	}
    	else {
    
    		// Set the filters
    		foreach ($params as $propertyId => $property) {
    			if (substr($propertyId, 0, 4) == 'min_') $where->greaterThanOrEqualTo('md_product_option.'.substr($propertyId, 4), $params[$propertyId]);
    			elseif (substr($propertyId, 0, 4) == 'max_') $where->lessThanOrEqualTo('md_product_option.'.substr($propertyId, 4), $params[$propertyId]);
    			else $where->like('md_product_option.'.$propertyId, '%'.$params[$propertyId].'%');
    		}
    	}
    
    	// Sort the list
    	$select->where($where)->order(array($major.' '.$dir, 'reference'));
    
    	$cursor = ProductOption::getTable()->selectWith($select);
    	$options = array();
    	foreach ($cursor as $option) {
    		$option->properties = $option->toArray();
    		$options[] = $option;
    	}
    
    	return $options;
    }
    
    public static function get($id)
    {
    	$productOption = ProductOption::getTable()->get($id);
    	$productOption->product = Product::get($productOption->product_id);
    	return $productOption;
    }

    public function loadData($data)
    {
    	$config = Context::getCurrent()->getConfig();
    	
    	$this->status = trim(strip_tags($data['status']));
    	$this->reference = trim(strip_tags($data['reference']));
    	$this->caption = trim(strip_tags($data['caption']));
    	$this->description = trim(strip_tags($data['description']));
    	$this->is_available = (int)$data['is_available'];
    
    	if ($this->status == '' || strlen($this->status) > 255) return 'Integrity';
    	if ($this->reference == '' || strlen($this->reference) > 255) return 'Integrity';
    	if ($this->caption == '' || strlen($this->caption) > 255) return 'Integrity';
    	if (strlen($this->description) > 2047) return 'Integrity';
    
    	// Prices
    	foreach ($config['ppitMasterDataSettings']['priceCategories'] as $category => $caption) {
    		$this->prices[$category] = (float) $data['price_'.$category];
    	}
    	
    	// Check consistency
    	$select = ProductOption::getTable()->getSelect()->where(array('reference' => $this->reference));
    	$cursor = ProductOption::getTable()->selectWith($select);
    	if (count($cursor) > 0 && $cursor->current()->id != $this->id) return 'Duplicate';

    	return 'OK';
    }
    
    public function loadDataFromRequest($request)
    {
    	$config = Context::getCurrent()->getConfig();

    	$data = array();
    	$data['status'] = $request->getPost('status');
    	$data['reference'] = $request->getPost('reference');
    	$data['caption'] = $request->getPost('caption');
    	$data['description'] = $request->getPost('description');
    	$data['is_available'] = $request->getPost('is_available');

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
    	ProductOption::getTable()->save($this);
    	return 'OK';
    }
    
    public function update($update_time)
    {
    	$productOption = ProductOption::getTable()->get($this->id);
    	if ($productOption->update_time > $update_time) return 'Isolation';
    	ProductOption::getTable()->save($this);
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
    	$productOption = ProductOption::getTable()->get($this->id);
    	if ($productOption->update_time > $update_time) return 'Isolation';
    	ProductOption::getTable()->delete($this->id);
    
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
    	if (!ProductOption::$table) {
    		$sm = Context::getCurrent()->getServiceManager();
    		ProductOption::$table = $sm->get('PpitMasterData\Model\ProductOptionTable');
    	}
    	return ProductOption::$table;
    }
}
