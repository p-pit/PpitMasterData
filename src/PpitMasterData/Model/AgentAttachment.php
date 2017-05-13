<?php
namespace PpitMasterData\Model;

use PpitCore\Model\Context;
use PpitCore\Model\Vcard;
use PpitMasterData\Model\Agent;
use Zend\Db\Sql\Where;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class AgentAttachment implements InputFilterAwareInterface
{
    public $id;
    public $agent_id;
    public $org_unit_id;
    public $effective_date;
    protected $inputFilter;

    // Joined properties
    public $n_fn;
    
    // Transient properties
    public $agent;
    public $contact;
    
    // Static fields
    private static $table;
    
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function exchangeArray($data)
    {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->agent_id = (isset($data['agent_id'])) ? $data['agent_id'] : null;
        $this->org_unit_id = (isset($data['org_unit_id'])) ? $data['org_unit_id'] : null;
      	$this->effective_date = (isset($data['effective_date']) && $data['effective_date'] != '9999-12-31') ? $data['effective_date'] : null;
      	 
    	// Joined properties
        $this->n_fn = (isset($data['n_fn'])) ? $data['n_fn'] : null;
    }

    public function getProperties()
    {
    	$data = array();
    	$data['id'] = (int) $this->id;
    	$data['agent_id'] =  (int) $this->agent_id;
    	$data['org_unit_id'] = (int) $this->org_unit_id;
    	$data['effective_date'] = ($this->effective_date) ? $this->effective_date : null;
    	return $data;
    }

    public function toArray()
    {
    	$data = $this->getProperties();
    	if (!$data['effective_date']) $data['effective_date'] = '9999-12-31';
    	return $data;
    }

    /**
     * Returns an array of AgentAttachments:
     * - filtering the list on active attachents (effective_date less or equal to the the current date) if $mode == 'todo'
     * - matching (with the 'like' sql comparator) the key-value pairs provided in the params argument otherwise
     * The result is primarily ordered according to the value of $major with the direction (ASC or DESC) specified by $dir, and secondarily by 'n_fn' (joined from core_vcard via core_agent.contact_id)
     * @param array $params
     * @param string $major
     * @param string $dir
     * @param string $mode
     * @return AgentAttachment[]
     */
    public static function getList($params, $major, $dir, $mode = 'todo')
    {
    	$select = AgentAttachment::getTable()->getSelect()
    	->join('core_agent', 'core_agent_attachment.agent_id = core_agent.id', array(), 'left')
    	->join('core_vcard', 'core_agent.contact_id = core_vcard.id', array('n_fn'), 'left')
    	->order(array($major.' '.$dir, 'core_vcard.n_fn'));
    	$where = new Where;
    	$where->notEqualTo('core_agent_attahchment.status', 'deleted');
    
    	// Todo list vs search modes
    	if ($mode == 'todo') {
    		$where->lessThanOrEqualTo('effective_date', date('Y-m-d'));
    	}
    	else {
    		foreach ($params as $propertyId => $property) {
    			if (substr($propertyId, 0, 4) == 'min_') $where->greaterThanOrEqualTo('core_agent_attachment.'.substr($propertyId, 4), $params[$propertyId]);
    			elseif (substr($propertyId, 0, 4) == 'max_') $where->lessThanOrEqualTo('core_agent_attachment.'.substr($propertyId, 4), $params[$propertyId]);
    			else $where->like('core_agent_attachment.'.$propertyId, '%'.$params[$propertyId].'%');
    		}
    	}
    	$select->where($where);
    	$cursor = AgentAttachment::getTable()->selectWith($select);
    	$agentAttachments = array();
    	foreach ($cursor as $agentAttachment) $agentAttachments[] = $agentAttachment;
    	return $agentAttachments;
    }
     
    /**
     * Retrieve the agent attachment having the giving value as the given specified column ('id' as a default).
     * @param int $id
     * @param string $column
     * @return AgentAttachment
     */
    public static function get($id, $column = 'id')
    {
    	$context = Context::getCurrent();
    	$agentAttachment = AgentAttachment::getTable()->get($id, $column);
    	$agent = Agent::get($agentAttachment->agent_id);
    	$agentAttachment->agent = $agent;
    	if ($agent->contact_id) {
    		$contact = Vcard::get($agent->contact_id);
    		$agentAttachment->contact = $contact;
    		$agentAttachment->n_fn = $contact->n_fn;
    	}
    	return $agentAttachment;
    }
    
    /**
     * Return a new agent attachment.
     * Appropriately initializes the properties: status is set to 'new'.
     * @return AgentAttachment
     */
    public static function instanciate()
    {
    	$context = Context::getCurrent();
    	$agentAttachment = new AgentAttachment;
    	$agentAttachment->status = 'new';
    	return $agentAttachment;
    }
    
    /**
     * Loads the data into the AgentAttachment object depending of an array, typically constructed by the controller with value extracted from an HTTP request.
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
    	if (array_key_exists('agent_id', $data)) {
    		$agent_id = (int) $data['agent_id'];
    		if ($this->agent_id != $agent_id) $auditRow['agent_id'] = $this->agent_id = $agent_id;
    	}
    	if (array_key_exists('effective_date', $data)) {
    		$effective_date = $data['effective_date'];
    		if ($effective_date && !checkdate(substr($effective_date, 5, 2), substr($effective_date, 8, 2), substr($effective_date, 0, 4))) return 'Integrity';
    		if ($this->effective_date != $effective_date) $auditRow['effective_date'] = $this->effective_date = $effective_date;
    	}
    	$this->audit[] = $auditRow;
    	return 'OK';
    }
    
    /**
     * Adds a new row in the database.
     * @return string
     */
    public function add()
    {
    	$context = Context::getCurrent();
    	$this->id = null;
    	AgentAttachment::getTable()->save($this);
    
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
    	$agentAttachment = AgentAttachment::get($this->id);
    
    	// Isolation check
    	if ($update_time && $agent->update_time > $update_time) return 'Isolation';
    
    	AgentAttachment::getTable()->save($this);
    
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
    		$connection = AgentAttachment::getTable()->getAdapter()->getDriver()->getConnection();
    		$connection->beginTransaction();
    		try {
    			if ($data['action'] == 'update' || $data['action'] == 'delete') $agentAttachment = AgentAttachment::get($data['identifier'], 'identifier');
    			elseif ($data['action'] == 'add') $agentAttachment = AgentAttachment::instanciate();
    			if ($data['action'] == 'delete') $rc = $agentAttachment->delete(null);
    			else {
    				$data['agent_id'] = Agent::get($data['agent_identifier'], 'identifier')->id;
    				$data['org_unit_id'] = OrgUnit::get($data['org_unit_identifier'], 'identifier')->id;
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
    	$agentAttachment = AgentAttachment::get($this->id);
    	if ($update_time && $agentAttachment->update_time > $update_time) return 'Isolation';
    	AgentAttachment::getTable()->delete($this->id);
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
    	if (!AgentAttachment::$table) {
    		$sm = Context::getCurrent()->getServiceManager();
    		AgentAttachment::$table = $sm->get('PpitCore\Model\AgentAttachmentTable');
    	}
    	return AgentAttachment::$table;
    }
}
