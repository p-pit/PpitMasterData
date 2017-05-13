<?php
namespace PpitMasterData\Model;

use PpitCore\Model\Community;
use PpitCore\Model\Context;
use PpitCore\Model\Generic;
use PpitCore\Model\Place;
use PpitMasterData\Model\OrgUnitContact;
use Zend\Db\Sql\Where;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * The OrgUnit class implements a tree of organizational units related to a given 2pit logical instance.
 * Organizational units define perimeters of responsabilities. Contacts (\PpitCore\Model\Vcard) are assigned roles on organization units.
 * For example, a given organization unit can represent a service and a given contact can be linked as 'accountable' to this entity.
 * An object of type OrgUnit is a node of this link and maintains a foreign key to its parent (which is null if the node is the tree root).
 * Organizational units can be given a type depending on specific organizational semantic (for example, direction, division, service...).
 * Organizational units can be linked to a place (\PpitCore\Model\Place). 
 * The caption and description of organizational units can be expressed in the default locale for this instance and in two optional additional locales
 */
class OrgUnit implements InputFilterAwareInterface
{
	/** @var int */ public $id;
    /** @var int */ public $instance_id;
    /** @var string */ public $status;
    /** @var string */ public $locale_1;
    /** @var string */ public $locale_2;
    /** @var string */ public $type;
    /** @var string */ public $identifier;
    /** @var int */ public $place_id;
    /** @var int */ public $parent_id;
    /** @var string */ public $caption;
    /** @var string */ public $caption_locale_1;
    /** @var string */ public $caption_locale_2;
    /** @var string */ public $description;
    /** @var string */ public $description_locale_1;
    /** @var string */ public $description_locale_2;
    /** @var boolean */ public $is_open;
    /** @var array */ public $audit;
    /** @var string */ public $update_time;
    
    // Joined properties
    /** @var string */ public $place_identifier;
    /** @var string */ public $place_caption;
    
    // Transient properties
    /** @var \PpitCore\Model\Place */ public $place;
    /** @var \PpitCore\Model\OrgUnit */ public $parent;
    
    /** 
     * Ignored 
     * @var string 
     */ 
	protected $inputFilter;

    // Static fields
    /** @var \PpitCore\Model\Community */ private static $table;
    
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

	/**
	 * Used for relational (database) to object (php) mapping.
	 * @param array data
	 */
    public function exchangeArray($data)
    {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->status = (isset($data['status'])) ? $data['status'] : null;
        $this->locale_1 = (isset($data['locale_1'])) ? $data['locale_1'] : null;
        $this->locale_2 = (isset($data['locale_2'])) ? $data['locale_2'] : null;
        $this->type = (isset($data['type'])) ? $data['type'] : null;
        $this->identifier = (isset($data['identifier'])) ? $data['identifier'] : null;
        $this->place_id = (isset($data['place_id'])) ? $data['place_id'] : null;
        $this->parent_id = (isset($data['parent_id'])) ? $data['parent_id'] : null;
        $this->caption = (isset($data['caption'])) ? $data['caption'] : null;
        $this->caption_locale_1 = (isset($data['caption_locale_1'])) ? $data['locale_1'] : null;
        $this->caption_locale_2 = (isset($data['caption_locale_2'])) ? $data['locale_2'] : null;
        $this->description = (isset($data['description'])) ? $data['description'] : null;
        $this->description_locale_1 = (isset($data['description_locale_1'])) ? $data['description_locale_1'] : null;
        $this->description_locale_2 = (isset($data['description_locale_2'])) ? $data['description_locale_2'] : null;
        $this->is_open = (isset($data['is_open'])) ? $data['is_open'] : null;
        $this->audit = (isset($data['audit'])) ? json_decode($data['audit'], true) : null;
        $this->update_time = (isset($data['update_time'])) ? $data['update_time'] : null;
        
        // Joined properties
        $this->place_identifier = (isset($data['place_identifier'])) ? $data['place_identifier'] : null;
        $this->place_caption = (isset($data['place_caption'])) ? $data['place_caption'] : null;
    }

    /**
     * Provides an array-style access to properties for generic and configurable algorythms at the controller and view layers.
     * @return array
     */
    public function getProperties() {
    	$data = array();
    	$data['id'] = (int) $this->id;
    	$data['instance_id'] = (int) $this->instance_id;
    	$data['status'] = $this->status;
    	$data['locale_1'] = $this->locale_1;
    	$data['locale_2'] = $this->locale_2;
    	$data['type'] = $this->type;
    	$data['identifier'] = $this->identifier;
    	$data['place_id'] = (int) $this->place_id;
    	$data['parent_id'] = (int) $this->parent_id;
    	$data['caption'] = $this->caption;
    	$data['caption_locale_1'] = $this->caption_locale_1;
    	$data['caption_locale_2'] = $this->caption_locale_2;
    	$data['description'] = $this->description;
    	$data['description_locale_1'] = $this->description_locale_1;
    	$data['description_locale_2'] = $this->description_locale_2;
    	$data['is_open'] = (int) $this->is_open;
    	$data['audit'] = $this->audit;
    	return $data;
    }
    
	/**
	 * Used for object (php) to relational (database) mapping.
	 * The difference between getProperties() and toArray() is that getProperties does not transform data while toArray do sometime.
	 * For example, toArray() converts arrays to JSON.
	 * @return array
	 */
    public function toArray() {
    	$data = $this->getProperties();
    	$data['audit'] = json_encode($this->audit);
    	return $data;
	}

    /**
     * Returns an array of OrgUnit:
     * - filtering the list on open units only if $mode == 'todo'
     * - matching (with the 'like' sql comparator) the key-value pairs provided in the params argument otherwise
     * The result is primarily ordered according to the value of $major with the direction (ASC or DESC) specified by $dir, and secondarily by 'type' and 'identifier'
     * @param array $params
     * @param string $major
     * @param string $dir
     * @param string $mode
     * @return OrgUnit[]
     */
	public static function getList($params, $major, $dir, $mode = 'todo')
	{
		$select = OrgUnit::getTable()->getSelect()
			->join('core_place', 'core_org_unit.place_id = core_place.id', array('place_identifier' => 'identifier', 'place_caption' => 'caption'), 'left')
			->order(array($major.' '.$dir, 'type', 'identifier'));
		$where = new Where;
    	$where->notEqualTo('core_org_unit.status', 'deleted');

    	// Todo list vs search modes
    	if ($mode == 'todo') {
    		$where->equalTo('is_open', '1');
    	}
    	else {
			foreach ($params as $propertyId => $property) {
    			if (substr($propertyId, 0, 4) == 'min_') $where->greaterThanOrEqualTo('core_org_unit.'.substr($propertyId, 4), $params[$propertyId]);
    			elseif (substr($propertyId, 0, 4) == 'max_') $where->lessThanOrEqualTo('core_org_unit.'.substr($propertyId, 4), $params[$propertyId]);
    			else $where->like('core_org_unit.'.$propertyId, '%'.$params[$propertyId].'%');
			}
    	}
		$select->where($where);
		$cursor = OrgUnit::getTable()->selectWith($select);
		$orgUnits = array();
		foreach ($cursor as $orgUnit) $orgUnits[] = $orgUnit;
		return $orgUnits;
	}

    /**
	 * Retrieve the organizational units the given contact is authorized on (whatever role he has on the unit)
     * @param int $contact_id
     * @param string $role
     * @return OrgUnit[]
     */
	public static function retrievePerimeter($contact_id, $role = null)
	{
		$select = OrgUnit::getTable()->getSelect()
			->join('core_org_unit_contact', 'core_org_unit.id = core_org_unit_contact.org_unit_id', array(), 'left')
			->join('core_place', 'core_org_unit.place_id = core_place.id', array('place_identifier' => 'identifier', 'place_caption' => 'caption'), 'left')
			->where(array('contact_id' => $contact_id, 'role' => $role, 'is_open' => true));
		$where = new Where;
    	$where->notEqualTo('core_org_unit.status', 'deleted');
    	$where->equalTo('is_open', '1');
    	if ($role) $where->equalTo('role', $role);
    	$cursor = OrgUnit::getTable()->selectWith($select);
		$orgUnits = array();
		foreach ($cursor as $orgUnit) $orgUnits[$orgUnit->id] = $orgUnit;
		return $orgUnits;
	}

    /**
     * Retrieve the organizational units having the giving value as the given specified column ('id' as a default).
     * In 'config' specification mode, the specifications property is overwritten with the current config value (for testing new specific value of parameters)
     * @param int $id
     * @param string $column
     * @return OrgUnit
     */
	public static function get($id, $column = 'id')
	{
		$context = Context::getCurrent();
		$orgUnit = OrgUnit::getTable()->get($id, $column);
		if ($orgUnit->place_id) {
			$place = Place::get($orgUnit->place_id);
			$orgUnit->place = $place;
			$orgUnit->place_identifier = $place->identifier;
			$orgUnit->place_caption = $place->caption;
		}
		return $orgUnit;
	}

    /**
     * Return a nex organization Unit.
     * Appropriately initializes the properties: status is set to 'new' and the unit is opened ($is_open boolean).
     * Link the OrgUnit to the given place and parent OrhUnit if given.
     * @param string $type
     * @param int $parent_id
     * @param int $place_id
     * @return OrgUnit
     */
	public static function instanciate()
	{
		$context = Context::getCurrent();
		$orgUnit = new OrgUnit;
		$orgUnit->status = 'new';
		$orgUnit->is_open = true;
		return $orgUnit;
	}

    /**
     * Loads the data into the OrgUnit object depending of an array, typically constructed by the controller with value extracted from an HTTP request.
     * Only the properties present as a key in the argument array are updated in the target object.
     * As a protection against bugs or attacks from the view level, every string property are trimed, cleaned of tags and checked against max length.
     * If the protection check failed, the method returns the string 'Integrity' otherwise it returns the string 'OK'.
     * The audit database property is augmented with a row storing the current date and time, the user doing the update, and the list of updated properties along with the new value.
     * @param array $data
     * @return string
     */
	public function loadData($data)
	{
		$context = Context::getCurrent();
		$auditRow = array(
				'time' => Date('Y-m-d G:i:s'),
				'n_fn' => $context->getFormatedName(),
		);
		if (array_key_exists('status', $data)) {
			$status = trim(strip_tags($data['status']));
			if ($status == '' || strlen($status) > 255) return 'Integrity';
			if ($this->status != $status) $auditRow['status'] = $this->status = $status;
		}
		if (array_key_exists('locale_1', $data)) {
			$locale_1 = trim(strip_tags($data['locale_1']));
			if ($locale_1 == '' || strlen($locale_1) > 5) return 'Integrity';
			if ($this->locale_1 != $locale_1) $auditRow['locale_1'] = $this->locale_1 = $locale_1;
		}
		if (array_key_exists('locale_2', $data)) {
			$locale_2 = trim(strip_tags($data['locale_2']));
			if ($locale_2 == '' || strlen($locale_2) > 5) return 'Integrity';
			if ($this->locale_2 != $locale_2) $auditRow['locale_2'] = $this->locale_2 = $locale_2;
		}
		if (array_key_exists('type', $data)) {
			$type = trim(strip_tags($data['type']));
			if ($type == '' || strlen($type) > 255) return 'Integrity';
			if ($this->type != $type) $auditRow['type'] = $this->type = $type;
		}
		if (array_key_exists('identifier', $data)) {
			$identifier = trim(strip_tags($data['identifier']));
			if ($identifier == '' || strlen($identifier) > 255) return 'Integrity';
			if ($this->identifier != $identifier) $auditRow['identifier'] = $this->identifier = $identifier;
		}
		if (array_key_exists('place_id', $data)) {
			$place_id = (int) $data['place_id'];
			if ($this->place_id != $place_id) $auditRow['place_id'] = $this->place_id = $place_id;
		}
		if (array_key_exists('parent_id', $data)) {
			$parent_id = (int) $data['parent_id'];
			if ($this->parent_id != $parent_id) $auditRow['parent_id'] = $this->parent_id = parent_id;
		}
		if (array_key_exists('caption', $data)) {
			$caption = trim(strip_tags($data['caption']));
			if ($caption == '' || strlen($caption) > 255) return 'Integrity';
			if ($this->caption != $caption) $auditRow['caption'] = $this->caption = $caption;
		}
		if (array_key_exists('caption_locale_1', $data)) {
			$caption_locale_1 = trim(strip_tags($data['caption_locale_1']));
			if ($caption_locale_1 == '' || strlen($caption_locale_1) > 255) return 'Integrity';
			if ($this->caption_locale_1 != $caption_locale_1) $auditRow['caption_locale_1'] = $this->caption_locale_1 = $caption_locale_1;
		}
		if (array_key_exists('caption_locale_2', $data)) {
			$caption_locale_2 = trim(strip_tags($data['caption_locale_2']));
			if ($caption_locale_2 == '' || strlen($caption_locale_2) > 255) return 'Integrity';
			if ($this->caption_locale_2 != $caption_locale_2) $auditRow['caption_locale_2'] = $this->caption_locale_2 = $caption_locale_2;
		}
		if (array_key_exists('description', $data)) {
			$description = trim(strip_tags($data['description']));
			if ($description == '' || strlen($description) > 2047) return 'Integrity';
			if ($this->description != $description) $auditRow['description'] = $this->description = $description;
		}
		if (array_key_exists('description_locale_1', $data)) {
			$description_locale_1 = trim(strip_tags($data['description_locale_1']));
			if ($description_locale_1 == '' || strlen($description_locale_1) > 2047) return 'Integrity';
			if ($this->description_locale_1 != $description_locale_1) $auditRow['description_locale_1'] = $this->description_locale_1 = $description_locale_1;
		}
		if (array_key_exists('description_locale_2', $data)) {
			$description_locale_2 = trim(strip_tags($data['description_locale_2']));
			if ($description_locale_2 == '' || strlen($description_locale_2) > 2047) return 'Integrity';
			if ($this->description_locale_2 != $description_locale_2) $auditRow['description_locale_2'] = $this->description_locale_2 = $description_locale_2;
		}
		if (array_key_exists('is_open', $data)) {
			$is_open = (int) $data['is_open'];
			if ($this->is_open != $is_open) $auditRow['is_open'] = $this->is_open = $is_open;
		}
		$this->audit[] = $auditRow;
		return 'OK';
	}

    /**
     * Adds a new row in the database after checking that it does not conflict with an existing OrgUnit with the same 'identifier'. 
     * In such a case the methods does not affect the database and returns 'Duplicate', otherwise it returns 'OK'.
     * @return string
     */
	public function add()
	{
		$context = Context::getCurrent();

		// Check consistency
		if (Generic::getTable()->cardinality('core_org_unit', array('identifier' => $this->identifier, 'status != ?' => 'deleted')) > 0) return 'Duplicate';
		$this->id = null;
		OrgUnit::getTable()->save($this);

		return ('OK');
	}
	
    /**
     * Update an existing row in the database.
     * If $update_time is provided, an isolation check is performed, such that the current update time in the database is not greater than the one given as an argument.
     * In such a case the methods does not affect the database and returns 'Isolation', otherwise it returns 'OK'.
     * @param string $update_time
     * @return string
     */
	public function update($update_time)
	{
		$context = Context::getCurrent();
		$orgUnit = OrgUnit::get($this->id);
		
		// Isolation check
		if ($update_time && $orgUnit->update_time > $update_time) return 'Isolation';

		OrgUnit::getTable()->save($this);
		
		return 'OK';
	}

	/**
	 * @param Interaction $interaction
	 * @return string
	 */
	public static function processInteraction($interaction)
	{
		$context = Context::getCurrent();
		$content = json_decode($interaction->content, true);
		$globalRc = 'OK';
		$newContent = array();
		foreach ($content as $data) {
	    	$connection = OrgUnit::getTable()->getAdapter()->getDriver()->getConnection();
	    	$connection->beginTransaction();
	    	try {
				if ($data['action'] == 'update' || $data['action'] == 'delete') $orgUnit = OrgUnit::get($data['identifier'], 'identifier');
				elseif ($data['action'] == 'add') $orgUnit = OrgUnit::instanciate();
				if ($data['action'] == 'delete') $rc = $orgUnit->delete(null);
				else {
					if (array_key_exists('place_identifier', $data)) {
						$place = Place::get($data['place_identifier'], 'identifier');
						if ($place) $data['place_id'] = $place->id;
					}
					if ($orgUnit->loadData($data) != 'OK') throw new \Exception('View error');
		    		if (!$orgUnit->id) $rc = $orgUnit->add();
		    		else $rc = $orgUnit->update(null);
		    		$data['result'] = $rc;
	    			if ($rc != 'OK') {
	    				$globalRc = 'partial';
	    				$connection->rollback();
	    			}
		    		else $connection->commit();
		    	}
	    	}
    		catch (\Exception $e) {
    			$connection->rollback();
    			throw $e;
    		}
    		$newContent[] = $data;
		}
		$interaction->content = json_encode($newContent);
		$interaction->update(null);
		return $globalRc;
	}
	
    /**
     * Delete the row in the database
     * If $update_time is provided, an isolation check is performed, such that the current update time in the database is not greater than the one given as an argument.
     * In such a case the methods does not affect the database and returns 'Isolation', otherwise it returns 'OK'.
     */
	public function delete($update_time)
	{
		$context = Context::getCurrent();
		$orgUnit = OrgUnit::get($this->id);
		
		// Isolation check
		if ($update_time && $orgUnit->update_time > $update_time) return 'Isolation';
		
		OrgUnit::getTable()->delete($this->id);
		
		// Delete the links to contacts
		OrgUnitContact::getTable()->multipleDelete(array('org_unit_id' => $this->id));

		return 'OK';
	}
	
	/**
	 * Not used in P-Pit
	 * {@inheritDoc}
	 * @see \Zend\InputFilter\InputFilterAwareInterface::setInputFilter()
	 */
	public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    /**
     * Not used in P-Pit
     * {@inheritDoc}
     * @see \Zend\InputFilter\InputFilterAwareInterface::getInputFilter()
     */
    public function getInputFilter()
    {
        throw new \Exception("Not used");
    }

    /**
     * Returns the object to relational manager for the OrgUnit class
     */
    public static function getTable()
    {
    	if (!OrgUnit::$table) {
    		$sm = Context::getCurrent()->getServiceManager();
    		OrgUnit::$table = $sm->get('PpitCore\Model\OrgUnitTable');
    	}
    	return OrgUnit::$table;
    }
}
