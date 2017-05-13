<?php
namespace PpitMasterData\Model;

use PpitCore\Model\Context;
use Zend\Db\Sql\Where;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Test implements InputFilterAwareInterface
{
    public $id;
    public $instance_id;
    public $status;
    public $identifier;
    public $contact_id;
    public $start_date;
    public $end_date;
    public $audit;
    
    // Joined properties
    public $n_fn;

    // Transient properties
    public $contact;
    
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
        $this->status = (isset($data['status'])) ? $data['status'] : null;
        $this->identifier = (isset($data['identifier'])) ? $data['identifier'] : null;
        $this->contact_id = (isset($data['contact_id'])) ? $data['contact_id'] : null;
      	$this->start_date = (isset($data['start_date'])) ? $data['start_date'] : null;
      	$this->end_date = (isset($data['end_date']) && $data['end_date'] != '9999-12-31') ? $data['end_date'] : null;
      	$this->audit = (isset($data['audit'])) ? json_decode($data['audit'], true) : null;
    
    	// Joined properties
        $this->n_fn = (isset($data['n_fn'])) ? $data['n_fn'] : null;
    }

    public function getProperties()
    {
    	$data = array();
    	$data['id'] = (int) $this->id;
    	$data['instance_id'] = (int) $this->instance_id;
    	$data['status'] =  $this->status;
    	$data['identifier'] =  $this->identifier;
    	$data['contact_id'] = (int) $this->contact_id;
    	$data['start_date'] = ($this->start_date) ? $this->start_date : null;
    	$data['end_date'] = ($this->end_date) ? $this->end_date : null;
    	$data['audit'] = $this->audit;
    	return $data;
    }
    
    public function toArray()
    {
    	$data = $this->getProperties();
    	if (!$data['end_date']) $data['end_date'] = '9999-12-31';
    	$data['audit'] = ($this->audit) ? json_encode($this->audit) : null;
    	return $data;
    }

    /**
     * Returns an array of Agents:
     * - filtering the list on active agents (begin_date and end_date framing the current date) if $mode == 'todo'
     * - matching (with the 'like' sql comparator) the key-value pairs provided in the params argument otherwise
     * The result is primarily ordered according to the value of $major with the direction (ASC or DESC) specified by $dir, and secondarily by 'identifier'
     * @param array $params
     * @param string $major
     * @param string $dir
     * @param string $mode
     * @return Agent[]
     */
    public static function getList($params, $major, $dir, $mode = 'todo')
    {
    	$select = Agent::getTable()->getSelect()
	    	->join('core_vcard', 'core_agent.contact_id = core_vcard.id', array('n_fn'), 'left')
    		->order(array($major.' '.$dir, 'identifier'));
    	$where = new Where;
    	$where->notEqualTo('core_agent.status', 'deleted');
    
    	// Todo list vs search modes
    	if ($mode == 'todo') {
    		$where->lessThanOrEqualTo('begin_date', date('Y-m-d'));
    		$where->greaterThanOrEqualTo('end_date', date('Y-m-d'));
    	}
    	else {
    		foreach ($params as $propertyId => $property) {
    			if (substr($propertyId, 0, 4) == 'min_') $where->greaterThanOrEqualTo('core_agent.'.substr($propertyId, 4), $params[$propertyId]);
    			elseif (substr($propertyId, 0, 4) == 'max_') $where->lessThanOrEqualTo('core_agent.'.substr($propertyId, 4), $params[$propertyId]);
    			else $where->like('core_agent.'.$propertyId, '%'.$params[$propertyId].'%');
    		}
    	}
    	$select->where($where);
    	$cursor = Agent::getTable()->selectWith($select);
    	$agents = array();
    	foreach ($cursor as $agent) $agents[] = $agent;
    	return $agents;
    }
   
    /**
     * Retrieve the agent having the giving value as the given specified column ('id' as a default).
     * @param int $id
     * @param string $column
     * @return Agent
     */
    public static function get($id, $column = 'id')
    {
    	$context = Context::getCurrent();
    	$agent = Agent::getTable()->get($id, $column);
    	if ($agent->contact_id) {
    		$contact = Vcard::get($agent->contact_id);
    		$agent->contact = $contact;
    		$agent->n_fn = $contact->n_fn;
    	}
    	return $agent;
    }
    
    /**
     * Return a new agent.
     * Appropriately initializes the properties: status is set to 'new'.
     * @return Agent
     */
    public static function instanciate()
    {
    	$context = Context::getCurrent();
    	$agent = new Agent;
    	$agent->status = 'new';
    	return $agent;
    }
    
    /**
     * Loads the data into the Agent object depending of an array, typically constructed by the controller with value extracted from an HTTP request.
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
    	if (array_key_exists('identifier', $data)) {
    		$identifier = trim(strip_tags($data['identifier']));
    		if ($identifier == '' || strlen($identifier) > 255) return 'Integrity';
    		if ($this->identifier != $identifier) $auditRow['identifier'] = $this->identifier = $identifier;
    	}
    	if (array_key_exists('contact_id', $data)) {
    		$contact_id = (int) $data['contact_id'];
    		if ($this->contact_id != $contact_id) $auditRow['contact_id'] = $this->contact_id = $contact_id;
    	}
    	if (array_key_exists('start_date', $data)) {
			$start_date = $data['start_date'];
			if ($start_date && !checkdate(substr($start_date, 5, 2), substr($start_date, 8, 2), substr($start_date, 0, 4))) return 'Integrity';
    		if ($this->start_date != $start_date) $auditRow['start_date'] = $this->start_date = $start_date;
		}
		if (array_key_exists('end_date', $data)) {
			$end_date = $data['end_date'];
			if ($end_date && !checkdate(substr($end_date, 5, 2), substr($end_date, 8, 2), substr($end_date, 0, 4))) return 'Integrity';
    		if ($this->end_date != $end_date) $auditRow['end_date'] = $this->end_date = $end_date;
		}
    	$this->audit[] = $auditRow;
    	return 'OK';
    }
    
    /**
     * Adds a new row in the database after checking that it does not conflict with an existing Agent with the same 'identifier'.
     * In such a case the methods does not affect the database and returns 'Duplicate', otherwise it returns 'OK'.
     * @return string
     */
    public function add()
    {
    	$context = Context::getCurrent();
    
    	// Check consistency
    	if (Generic::getTable()->cardinality('core_agent', array('identifier' => $this->identifier, 'status != ?' => 'deleted')) > 0) return 'Duplicate';
    	$this->id = null;
    	Agent::getTable()->save($this);
    
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
    	$agent = Agent::get($this->id);
    
    	// Isolation check
    	if ($update_time && $agent->update_time > $update_time) return 'Isolation';
    
    	Agent::getTable()->save($this);
    
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
    		$connection = Agent::getTable()->getAdapter()->getDriver()->getConnection();
    		$connection->beginTransaction();
    		try {
    			if ($data['action'] == 'update' || $data['action'] == 'delete') $agent = Agent::get($data['identifier'], 'identifier');
    			elseif ($data['action'] == 'add') $agent = Agent::instanciate();
    			if ($data['action'] == 'delete') $rc = $agent->delete(null);
    			else {
    				if (array_key_exists('contact_n_first', $data) &&
    					array_key_exists('contact_n_last', $data) &&
    					(array_key_exists('contact_email', $data) ||
    					 array_key_exists('contact_tel_org', $data) ||
    					 array_key_exists('contact_tel_cell', $data))) {
    					$select = Vcard::getTable()->getSelect();
    					$where = new Where;
    					$where->like('n_first', $data['contact_n_first']);
    					$where->like('n_last', $data['contact_n_last']);
    					if (array_key_exists('contact_email', $data)) $where->like('email', $data['contact_email']);
    					elseif (array_key_exists('tel_org', $data)) $where->like('tel_org', $data['contact_tel_org']);
    					elseif (array_key_exists('tel_cell', $data)) $where->like('tel_cell', $data['contact_tel_cell']);
    					$select->where($where);
    					$cursor = Vcard::getTable()->selectWith($select);
    					foreach ($cursor as $contact) $data['contact_id'] = $contact->id;
    				}
    				if ($agent->loadData($data) != 'OK') throw new \Exception('View error');
    				if (!$agent->id) $rc = $agent->add();
    				else $rc = $agent->update(null);
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
    	$agent = Agent::get($this->id);
    
    	// Isolation check
    	if ($update_time && $agent->update_time > $update_time) return 'Isolation';
    
    	Agent::getTable()->delete($this->id);
    
    	// Delete the links to contacts
    	AgentAttachment::getTable()->multipleDelete(array('agent_id' => $this->id));
    
    	return 'OK';
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
    	if (!Agent::$table) {
    		$sm = Context::getCurrent()->getServiceManager();
    		Agent::$table = $sm->get('PpitCore\Model\AgentTable');
    	}
    	return Agent::$table;
    }
}
