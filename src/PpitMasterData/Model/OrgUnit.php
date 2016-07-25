<?php
namespace PpitMasterData\Model;

use PpitContact\Model\Community;
use PpitCore\Model\Context;
use PpitCore\Model\Generic;
use Zend\Db\Sql\Where;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class OrgUnit implements InputFilterAwareInterface
{
	public $id;
    public $instance_id;
    public $community_id;
    public $type;
    public $parent_id;
    public $identifier;
    public $caption;
    public $description;
    public $place_id;
    public $is_open;
    public $types;
    public $update_time;
    
    // Joined properties
    public $community_name;
    public $place_identifier;
    public $place_name;
    
    // Transient properties
    public $child_type;
    public $parent;
    
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
        $this->type = (isset($data['type'])) ? $data['type'] : null;
        $this->parent_id = (isset($data['parent_id'])) ? $data['parent_id'] : null;
        $this->identifier = (isset($data['identifier'])) ? $data['identifier'] : null;
        $this->caption = (isset($data['caption'])) ? $data['caption'] : null;
        $this->description = (isset($data['description'])) ? $data['description'] : null;
        $this->place_id = (isset($data['place_id'])) ? $data['place_id'] : null;
        $this->is_open = (isset($data['is_open'])) ? $data['is_open'] : null;
        $this->types = (isset($data['types'])) ? json_decode($data['types'], true) : null;
        $this->update_time = (isset($data['update_time'])) ? $data['update_time'] : null;
        
        // Joined properties
        $this->community_name = (isset($data['community_name'])) ? $data['community_name'] : null;
        $this->place_identifier = (isset($data['place_identifier'])) ? $data['place_identifier'] : null;
        $this->place_name = (isset($data['place_name'])) ? $data['place_name'] : null;
    }

    public function toArray() {

    	$data = array();
    	$data['id'] = (int) $this->id;
    	$data['instance_id'] = (int) $this->instance_id;
    	$data['community_id'] = (int) $this->community_id;
    	$data['type'] = $this->type;
    	$data['parent_id'] = (int) $this->parent_id;
    	$data['identifier'] = $this->identifier;
    	$data['caption'] = $this->caption;
    	$data['description'] = $this->description;
    	$data['place_id'] = (int) $this->place_id;
    	$data['is_open'] = (int) $this->is_open;
    	$data['types'] =  json_encode($this->types);
    	 
    	return $data;
	}

	public static function getList($major, $dir, $filter = array())
	{
		$select = OrgUnit::getTable()->getSelect()
			->join('contact_community', 'md_org_unit.community_id = contact_community.id', array('community_name' => 'name'), 'left')
			->join('md_place', 'md_org_unit.place_id = md_place.id', array('place_identifier' => 'identifier', 'place_name' => 'name'), 'left')
			->order(array($major.' '.$dir, 'type', 'identifier'));
		$where = new Where;
		foreach ($filter as $property => $value) {
			$where->like($property, '%'.$value.'%');
		}
		$select->where($where);
		$cursor = OrgUnit::getTable()->selectWith($select);
		$orgUnits = array();
		foreach ($cursor as $orgUnit) $orgUnits[] = $orgUnit;
		return $orgUnits;
	}

	public static function retrievePerimeter($contact_id/*, $role_id*/)
	{
		// Retrieve the organizational units the current contact is authorized on
		$select = OrgUnit::getTable()->getSelect()
			->join('md_org_unit_contact', 'md_org_unit.id = md_org_unit_contact.org_unit_id', array(), 'left')
			->where(array('contact_id' => $contact_id/*, 'role_id' => $role_id*/, 'is_open' => true));
		$cursor = OrgUnit::getTable()->selectWith($select);
		$orgUnits = array();
		foreach ($cursor as $orgUnit) $orgUnits[$orgUnit->id] = $orgUnit;
		return $orgUnits;
	}

	public static function get($id)
	{
		$context = Context::getCurrent();
		$orgUnit = OrgUnit::getTable()->get($id);

		// Access control : Restricted to the community in context perimeter
		if ($orgUnit->community_id != $context->getCommunityId()) return null;

		if ($orgUnit->parent_id) $orgUnit->parent = OrgUnit::get($orgUnit->parent_id);

		// Cache the child type
		for ($i = 0; $i < count($orgUnit->types); $i++) {
			if ($orgUnit->types[$i] == $orgUnit->type){
				if ($i < count($orgUnit->types) - 1) $orgUnit->child_type = $orgUnit->types[$i+1];
				break;
			}
		}

		// Retrieve the community data
		$community = Community::get($orgUnit->community_id);
		if ($community) $orgUnit->community_name = $community->name;

		// Retrieve the place of business data
		if ($orgUnit->place_id) {
			$place = Place::get($orgUnit->place_id);
			$orgUnit->place_identifier = $place->identifier;
			$orgUnit->place_name = $place->name;
		}
		return $orgUnit;
	}

	public static function instanciate($parent_id)
	{
		$context = Context::getCurrent();
		$orgUnit = new OrgUnit;
		$orgUnit->parent_id = $parent_id;
		// Retrieve the organisation unit types from parent or the default ones if root
		if ($parent_id) {
			$orgUnit->parent = OrgUnit::get($parent_id);
			$orgUnit->types = $orgUnit->parent->types;
			$orgUnit->type = $orgUnit->parent->child_type;
		}
		else {
			$orgUnit->types = $context->getConfig()['ppitMasterDataSettings']['defaultOrgUnitTypes'];
			$orgUnit->type = $orgUnit->types[0];
		}
		
		$community = Community::get($context->getCommunityId());
		if ($community) {
			$orgUnit->community_id = $community->id;
			$orgUnit->community_name = $community->name;
		}
		
		$orgUnit->is_open = true;

		return $orgUnit;
	}

	public function loadData($data)
	{
		$context = Context::getCurrent();
		
		$this->identifier = trim(strip_tags($data['identifier']));
		$this->caption = trim(strip_tags($data['caption']));
		$this->description = trim(strip_tags($data['description']));
		$this->place_id = (int) $data['place_id'];
		$this->is_open = (int) $data['is_open'];

		// Retrieve the applicable level in root add mode
		if (!$this->id && !$this->parent) {
			$types = array();
			foreach ($this->types as $type) if ($data['type_'.$type]) $types[] = $type;
			$this->types = $types;
			$this->type = $types[0];
		}
		
		// Check integrity
		if ($this->identifier == '' || strlen($this->identifier) > 255) return 'Integrity';
		if ($this->caption == '' || strlen($this->caption) > 255) return 'Integrity';
		if (strlen($this->description) > 2047) return 'Integrity';
		if ($this->place_id && !Place::get($this->place_id)) return 'Integrity';
		
		return 'OK';
	}
	
	public function loadDataFromRequest($request)
	{
		$data = array();
		$data['identifier'] = $request->getPost('identifier');
		$data['caption'] = $request->getPost('caption');
		$data['description'] = $request->getPost('description');
		$data['place_id'] = $request->getPost('place_id');
		$data['is_open'] = $request->getPost('is_open');
		
		// Retrieve the applicable level in root add mode
		if (!$this->id && !$this->parent) {
			foreach ($this->types as $type) $data['type_'.$type] = $request->getPost('type_'.$type);
		}

		$return = $this->loadData($data);
		if ($return != 'OK') throw new \Exception('View error');
		return $return;
	}
	
	public function add()
	{
		$context = Context::getCurrent();

		// Check consistency
		if (Generic::getTable()->cardinality('md_org_unit', array('community_id' => $context->getCommunityId(), 'identifier' => $this->identifier)) > 0) return 'Duplicate';
		
		$this->id = null;
		$this->community_id = $context->getCommunityId();
		OrgUnit::getTable()->save($this);

		return ('OK');
	}
	
	public function update($update_time)
	{
		$context = Context::getCurrent();
		$orgUnit = OrgUnit::get($this->id);

		// Access control : Restricted to the community in context perimeter
		if ($orgUnit->community_id != $context->getCommunityId()) return null;
		
		// Isolation check
		if ($orgUnit->update_time > $update_time) return 'Isolation';

		OrgUnit::getTable()->save($this);
		
		return 'OK';
	}

	public function isDeletable()
	{
		$context = Context::getCurrent();
		
		// Not deletable if the organisational unit has children
    	if (Generic::getTable()->cardinality('md_org_unit', array('parent_id' => $this->id)) > 0) return false;
	
		// Check other dependencies
		$config = $context->getConfig();
		foreach($config['ppitMasterDataDependencies'] as $dependency) {
			if ($dependency->isUsed($this)) return false;
		}
		
		return true;
	}
	
	public function delete($update_time)
	{
		$context = Context::getCurrent();
		$orgUnit = OrgUnit::get($this->id);

		// Access control : Restricted to the community in context perimeter
		if ($orgUnit->community_id != $context->getCommunityId()) return 'Unauthorized';
		
		// Isolation check
		if ($orgUnit->update_time > $update_time) return 'Isolation';
		
		OrgUnit::getTable()->delete($this->id);
		
		// Delete the links to contacts
		OrgUnitContact::getTable()->multipleDelete(array('org_unit_id' => $this->id));
		
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
    	if (!OrgUnit::$table) {
    		$sm = Context::getCurrent()->getServiceManager();
    		OrgUnit::$table = $sm->get('PpitMasterData\Model\OrgUnitTable');
    	}
    	return OrgUnit::$table;
    }
}
