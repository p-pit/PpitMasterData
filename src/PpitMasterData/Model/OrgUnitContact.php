<?php
namespace PpitMasterData\Model;

use PpitContact\Model\Vcard;
use PpitCore\Model\Context;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class OrgUnitContact implements InputFilterAwareInterface
{
	public $id;
	public $instance_id;
	public $org_unit_id;
	public $contact_id;
//	public $role_id;
	public $effect;
	public $update_time;
	
	// Joined properties
	public $org_unit_type;
	public $org_unit_identifier;
	public $org_unit_caption;
	public $n_fn;
	public $community_id;
	public $community_name;
	
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
		$this->org_unit_id = (isset($data['org_unit_id'])) ? $data['org_unit_id'] : null;
		$this->contact_id = (isset($data['contact_id'])) ? $data['contact_id'] : null;
//		$this->role_id = (isset($data['role_id'])) ? $data['role_id'] : null;
		$this->effect = (isset($data['effect'])) ? $data['effect'] : null;
		$this->update_time = (isset($data['update_time'])) ? $data['update_time'] : null;
		
		// Joined properties
		$this->org_unit_type = (isset($data['org_unit_type'])) ? $data['org_unit_type'] : null;
		$this->org_unit_identifier = (isset($data['org_unit_identifier'])) ? $data['org_unit_identifier'] : null;
		$this->org_unit_caption = (isset($data['org_unit_caption'])) ? $data['org_unit_caption'] : null;
		$this->n_fn = (isset($data['n_fn'])) ? $data['n_fn'] : null;
		$this->community_id = (isset($data['community_id'])) ? $data['community_id'] : null;
		$this->community_name = (isset($data['community_name'])) ? $data['community_name'] : null;
	}

	public function toArray() {
		
		$data = array();
		$data['id'] = (int) $this->id;
		$data['instance_id'] = (int) $this->instance_id;
		$data['org_unit_id'] = (int) $this->org_unit_id;
		$data['contact_id'] = (int) $this->contact_id;
//		$data['role_id'] = $this->role_id;
		$data['effect'] = ($this->effect) ? $this->effect : null;

		return $data;
	}
	
	public function getRoles()
	{
		return Context::getCurrent()->getConfig()['ppitMasterDataSettings']['defaultRoles'];
	}

	public static function getList($major, $dir, $org_unit_id = null)
	{
		$select = OrgUnitContact::getTable()->getSelect()
			->join('md_org_unit', 'md_org_unit_contact.org_unit_id = md_org_unit.id', array('community_id', 'org_unit_type' => 'type', 'org_unit_identifier' => 'identifier', 'org_unit_caption' => 'caption'), 'left')
			->join('contact_community', 'md_org_unit.community_id = contact_community.id', array('community_name' => 'name'), 'left')
			->join('contact_vcard', 'md_org_unit_contact.contact_id = contact_vcard.id', array('n_fn'), 'left')
			->order(array($major.' '.$dir, 'n_fn'/*, 'role_id'*/));
		if ($org_unit_id) $select->where(array('org_unit_id' => $org_unit_id));
		$cursor = OrgUnitContact::getTable()->selectWith($select);
		$orgUnitContacts = array();
		foreach ($cursor as $orgUnitContact) $orgUnitContacts[] = $orgUnitContact;
		return $orgUnitContacts;
	}
	
	public static function get($id)
	{
		$orgUnitContact = OrgUnitContact::getTable()->get($id);
		
		// Retrieve the org unit properties
		$orgUnit = OrgUnit::get($orgUnitContact->org_unit_id);
		$orgUnitContact->org_unit_type = $orgUnit->type;
		$orgUnitContact->org_unit_identifier = $orgUnit->identifier;
		$orgUnitContact->org_unit_caption = $orgUnit->caption;

		// Retrieve the formated contact name
		$contact = Vcard::get($orgUnitContact->contact_id);
		$orgUnitContact->n_fn = $contact->n_fn;
		
		return $orgUnitContact;
	}
	
	public static function instanciate($org_unit_id)
	{
		$orgUnitContact = new OrgUnitContact;
		$orgUnitContact->org_unit_id = $org_unit_id;
		return $orgUnitContact;
	}

	public function loadData($data)
	{
		$context = Context::getCurrent();
	
//		$this->role_id = trim(strip_tags($data['role_id']));
		$this->contact_id = trim(strip_tags($data['contact_id']));
		$this->effect = trim(strip_tags($data['effect']));

		// Check integrity
//		if ($this->role_id == '' || !array_key_exists($this->role_id, $this->getRoles())) return 'Integrity';
		if ($this->contact_id == '' || !Vcard::getTable()->get($this->contact_id)) return 'Integrity';
		if ($this->effect && !checkdate(substr($this->effect, 5, 2), substr($this->effect, 8, 2), substr($this->effect, 0, 4))) return 'Integrity';
		return 'OK';
	}
	
	public function loadDataFromRequest($request)
	{
		$data = array();
//		$data['role_id'] = $request->getPost('role_id');
		$data['contact_id'] = $request->getPost('contact_id');
		$data['effect'] = $request->getPost('effect');
		$return = $this->loadData($data);
		if ($return != 'OK') throw new \Exception('View error');
		return $return;
	}
	
	public function add()
	{
		$this->id = null;
		OrgUnitContact::getTable()->save($this);
		return 'OK';
	}
	
	public function save($update_time)
	{
		$orgUnitContact = OrgUnitContact::get($this->id);
		if ($orgUnitContact->update_time > $update_time) return 'Isolation';
		OrgUnitContact::getTable()->save($this);
		return 'OK';
	}
	
	public function isDeletable() {
		
		return true;
	}
	
	public function delete($update_time)
	{
		$orgUnitContact = OrgUnitContact::get($this->id);
		if ($orgUnitContact->update_time > $update_time) return 'Isolation';
		OrgUnitContact::getTable()->delete($this->id);
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
		if (!OrgUnitContact::$table) {
			$sm = Context::getCurrent()->getServiceManager();
			OrgUnitContact::$table = $sm->get('PpitMasterData\Model\OrgUnitContactTable');
		}
		return OrgUnitContact::$table;
	}
}
