<?php
namespace PpitMasterData\Model;

use PpitContact\Model\Community;
use PpitContact\Model\Vcard;
use PpitCore\Model\Context;
use PpitCore\Model\Generic;
use PpitCore\Model\Instance;
use Zend\Db\Sql\Where;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Place implements InputFilterAwareInterface
{
	public $id;
	public $instance_id;
	public $community_id;
	public $identifier;
	public $name;
	public $opening_date;
	public $closing_date;
	public $tax_regime;
	public $reception_contact_id;
	public $delivery_contact_id;
	public $properties = array();
	public $update_time;

	// Joined properties
	public $reception_n_fn;
	public $reception_email;
	public $reception_tel_work;
	public $reception_tel_cell;
	public $delivery_n_fn;
	public $delivery_email;
	public $delivery_tel_work;
	public $delivery_tel_cell;
	
	// Transient properties
	public $availableTaxRegimes;
	public $availableProperties;
	public $reception_contact;
	public $delivery_contact;
	public $is_open;
	
	// Depreciated
	public $customer_id;
	public $bill_contact_id;
	public $nb_people;
	public $surface;
	public $nb_floors;
	public $operational_hours;
	public $parking;
	public $lift;
	public $delivery_accessibility;
	public $security;
	public $logistic_constraints;
	
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
        $this->community_id = (isset($data['community_id'])) ? $data['community_id'] : null;
        $this->identifier = (isset($data['identifier'])) ? $data['identifier'] : null;
        $this->name = (isset($data['name'])) ? $data['name'] : null;
        $this->opening_date = (isset($data['opening_date'])) ? $data['opening_date'] : null;
        $this->closing_date = (isset($data['closing_date'])) ? $data['closing_date'] : null;
        $this->tax_regime = (isset($data['tax_regime'])) ? $data['tax_regime'] : null;
        $this->reception_contact_id = (isset($data['reception_contact_id'])) ? $data['reception_contact_id'] : null;
        $this->delivery_contact_id = (isset($data['delivery_contact_id'])) ? $data['delivery_contact_id'] : null;
        $this->properties = (isset($data['properties'])) ? json_decode($data['properties'], true) : null;
        $this->update_time = (isset($data['update_time'])) ? json_decode($data['update_time'], true) : null;
        
        // Joined properties
        $this->reception_n_fn = (isset($data['reception_n_fn'])) ? $data['reception_n_fn'] : null;
        $this->reception_email = (isset($data['reception_email'])) ? $data['reception_email'] : null;
        $this->reception_tel_work = (isset($data['reception_tel_work'])) ? $data['reception_tel_work'] : null;
        $this->reception_tel_cell = (isset($data['reception_tel_cell'])) ? $data['reception_tel_cell'] : null;
        $this->delivery_n_fn = (isset($data['delivery_n_fn'])) ? $data['delivery_n_fn'] : null;
        $this->delivery_email = (isset($data['delivery_email'])) ? $data['delivery_email'] : null;
        $this->delivery_tel_work = (isset($data['delivery_tel_work'])) ? $data['delivery_tel_work'] : null;
        $this->delivery_tel_cell = (isset($data['delivery_tel_cell'])) ? $data['delivery_tel_cell'] : null;
        
        // Depreciated
        $this->customer_id = (isset($data['customer_id'])) ? $data['customer_id'] : null;
        $this->bill_contact_id = (isset($data['bill_contact_id'])) ? $data['bill_contact_id'] : null;
        $this->nb_people = (isset($data['nb_people'])) ? $data['nb_people'] : null;
        $this->surface = (isset($data['surface'])) ? $data['surface'] : null;
        $this->nb_floors = (isset($data['nb_floors'])) ? $data['nb_floors'] : null;
        $this->operational_hours = (isset($data['operational_hours'])) ? $data['operational_hours'] : null;
        $this->parking = (isset($data['parking'])) ? $data['parking'] : null;
        $this->lift = (isset($data['lift'])) ? $data['lift'] : null;
        $this->delivery_accessibility = (isset($data['delivery_accessibility'])) ? $data['delivery_accessibility'] : null;
        $this->security = (isset($data['security'])) ? $data['security'] : null;
        $this->logistic_constraints = (isset($data['logistic_constraints'])) ? $data['logistic_constraints'] : null;
    }

    public function toArray()
    {
    	$data = array();
    	$data['id'] = (int) $this->id;
    	$data['community_id'] = (int) $this->community_id;
    	$data['identifier'] = $this->identifier;
    	$data['name'] = $this->name;
    	$data['opening_date'] = ($this->opening_date) ? $this->opening_date : null;
    	$data['closing_date'] = ($this->closing_date) ? $this->closing_date : null;
    	$data['tax_regime'] = $this->tax_regime;
    	$data['reception_contact_id'] = (int) $this->reception_contact_id;
    	$data['delivery_contact_id'] = (int) $this->delivery_contact_id;
    	$data['properties'] = json_encode($this->properties);
    	 
    	// Depreciated
    	$data['customer_id'] = (int) $this->customer_id;
    	$data['bill_contact_id'] = (int) $this->bill_contact_id;
    	$data['nb_people'] = (int) $this->nb_people;
    	$data['surface'] = (float) $this->surface;
    	$data['nb_floors'] = (int) $this->nb_floors;
    	$data['operational_hours'] = $this->operational_hours;
    	$data['parking'] = $this->parking;
    	$data['lift'] = $this->lift;
    	$data['delivery_accessibility'] = $this->delivery_accessibility;
    	$data['security'] = $this->security;
    	$data['logistic_constraints'] = $this->logistic_constraints;
    
    	return $data;
    }

    public static function getList($community_id = 0, $major = 'name', $dir = 'ASC', $filter = array())
    {
    	$select = Place::getTable()->getSelect()
    		->join(array('reception_contact' => 'contact_vcard'), 'md_place.reception_contact_id = reception_contact.id', array('reception_n_fn' => 'n_fn', 'reception_email' => 'email', 'reception_tel_work' => 'tel_work', 'reception_tel_cell' => 'tel_cell'), 'left')
    		->join(array('delivery_contact' => 'contact_vcard'), 'md_place.delivery_contact_id = delivery_contact.id', array('delivery_n_fn' => 'n_fn', 'delivery_email' => 'email', 'delivery_tel_work' => 'tel_work', 'delivery_tel_cell' => 'tel_cell'), 'left')
    		->order(array($major.' '.$dir, 'name', 'opening_date DESC'));
    	$where = new Where;
    	foreach ($filter as $property => $value) {
    		$where->like($property, '%'.$value.'%');
    	}
    	$select->where($where);

    	// Access control
/*    	if (!$instance_id) $select->where(array('instance_id' => $context->getInstanceId()));
    	else {
    		$instance = Instance::getTable()->get($instance_id);
    		if ($instance) $select->where(array('md_place.instance_id' => $instance_id));
    		else $select->where(array('md_place.instance_id' => $context->getInstanceId()));
    	}*/
    	$community = Community::get($community_id);
    	if ($community) $community_id = $community->id; else $community_id = 0;
    	$select->where(array('md_place.community_id' => $community_id));

    	$cursor = Place::getTable()->selectWith($select);
    	$places = array();
    	foreach ($cursor as $place) {
    		if ((!$place->opening_date || $place->opening_date <= Date('Y-m-d')) && (!$place->closing_date || $place->closing_date >= Date('Y-m-d'))) $place->is_open = true;
    		else $place->is_open = false;
    		$places[$place->id] = $place;
    	}
    	return $places;
    }
    
    public static function get($id, $column = 'id')
    {
    	$context = Context::getCurrent();
    	$place = Place::getTable()->get($id, $column);
    	$place->availableTaxRegimes = $context->getConfig()['ppitMasterDataSettings']['taxRegimes'];
    	$place->availableProperties = $context->getConfig()['ppitMasterDataSettings']['placeProperties'];

    	// Retrieve the reception contact
    	if ($place->reception_contact_id) $place->reception_contact = Vcard::getTable()->get($place->reception_contact_id);
    	else $place->reception_contact = new Vcard;
    	
    	// Retrieve the delivery contact
    	if ($place->delivery_contact_id) $place->delivery_contact = Vcard::getTable()->get($place->delivery_contact_id);
    	else $place->delivery_contact = new Vcard;

    	return $place;
    }
    
    public static function instanciate()
    {
    	$context = Context::getCurrent();
    	$place = new Place;
    	$place->availableTaxRegimes = $context->getConfig()['ppitMasterDataSettings']['taxRegimes'];
    	$place->availableProperties = $context->getConfig()['ppitMasterDataSettings']['placeProperties'];
    	$place->reception_contact = new Vcard;
    	$place->delivery_contact = new Vcard;
    	return $place;
	}

	public function loadData($data)
	{
    	$context = Context::getCurrent();
		$this->identifier = trim(strip_tags($data['identifier']));
		$this->name = trim(strip_tags($data['name']));
		$this->opening_date = $data['opening_date'];
		$this->closing_date = $data['closing_date'];
		$this->tax_regime = $data['tax_regime'];
		$this->reception_contact_id = (int) $data['reception_contact_id'];
		$this->delivery_contact_id = (int) $data['delivery_contact_id'];
		foreach ($context->getConfig()['ppitMasterDataSettings']['placeProperties'] as $property => $params) {
			$this->properties[$property] = $data[$property];
		}
		
		// Check integrity
		if ($this->identifier == '' || strlen($this->identifier) > 255) return 'Integrity';
		if ($this->name == '' || strlen($this->name) > 255) return 'Integrity';
		if ($this->opening_date && !checkdate(substr($this->opening_date, 5, 2), substr($this->opening_date, 8, 2), substr($this->opening_date, 0, 4))) return 'Integrity';
		if ($this->closing_date && !checkdate(substr($this->closing_date, 5, 2), substr($this->closing_date, 8, 2), substr($this->closing_date, 0, 4))) return 'Integrity';
		if (!array_key_exists($this->tax_regime, $context->getConfig()['ppitMasterDataSettings']['taxRegimes'])) return 'Integrity';
		return 'OK';
	}

	public function loadDataFromRequest($request)
	{
		$context = Context::getCurrent();
		$data = array();
		$data['identifier'] = $request->getPost('identifier');
		$data['name'] = $request->getPost('name');
		$data['opening_date'] = $request->getPost('opening_date');
		$data['closing_date'] = $request->getPost('closing_date');
		$data['tax_regime'] = $request->getPost('tax_regime');
		$data['reception_contact_id'] = $request->getPost('reception_contact_id');
		$data['delivery_contact_id'] = $request->getPost('delivery-contact_id');
		foreach ($context->getConfig()['ppitMasterDataSettings']['placeProperties'] as $property => $params) {
			$data[$property] = $request->getPost($property);
		}
		
		// Load the reception and delivery contacts data
		if ($request->getPost('reception_n_last')) $this->reception_contact->loadDataFromRequest($request, $context->getCommunityId(), 'reception_');
		if ($request->getPost('delivery_n_last')) $this->delivery_contact->loadDataFromRequest($request, $context->getCommunityId(), 'delivery_');
		$return = $this->loadData($data);
		if ($return != 'OK') throw new \Exception('View error');
	}
	
    public function add()
    {
		// Check consistency
		if (Generic::getTable()->cardinality('md_place', array('identifier' => $this->identifier)) > 0) return 'Duplicate';
    	
    	$this->id = null;
    	$this->reception_contact_id = Vcard::optimize($this->reception_contact)->id;
    	$this->delivery_contact_id = Vcard::optimize($this->delivery_contact)->id;
    	Place::getTable()->save($this);

		return ('OK');
    }

    public function update($update_time)
    {
    	$place = Place::get($this->id);
    	if ($place->update_time > $update_time) return 'Isolation';
    	if ($this->reception_contact->n_last) $this->reception_contact_id = Vcard::optimize($this->reception_contact)->id;
    	if ($this->delivery_contact->n_last) $this->delivery_contact_id = Vcard::optimize($this->delivery_contact)->id;
    	Place::getTable()->save($this);

		return ('OK');
    }

    public function isUsed($object)
    {
    	// Allow or not deleting a contact
    	if (get_class($object) == 'PpitContact\Model\Vcard') {
    		if (Generic::getTable()->cardinality('md_place', array('reception_contact_id' => $object->id)) > 0) return true;
    		if (Generic::getTable()->cardinality('md_place', array('delivery_contact_id' => $object->id)) > 0) return true;
    	}
    	return false;
    }
    
	public function isDeletable() {
    
    	// Not deletable if an organisational unit is linked to
    	if (Generic::getTable()->cardinality('md_org_unit', array('place_id' => $this->id)) > 0) return false;
    
    	// Check other dependencies
    	$config = Context::getCurrent()->getConfig();
    	foreach($config['ppitMasterDataDependencies'] as $dependency) {
    		if ($dependency->isUsed($this)) return false;
    	}
		
		return true;
	}
    
    public function delete($update_time)
    {
    	$place = Place::get($this->id);
    	if ($place->update_time > $update_time) return 'Isolation';
    	Place::getTable()->delete($this->id);

		return ('OK');
    }
	
    // Add content to this method:
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
    	if (!Place::$table) {
    		$sm = Context::getCurrent()->getServiceManager();
    		Place::$table = $sm->get('PpitMasterData\Model\PlaceTable');
    	}
    	return Place::$table;
    }
}
