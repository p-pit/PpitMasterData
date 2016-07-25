<?php
namespace PpitMasterData\Model;

use PpitCore\Model\Context;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Agent implements InputFilterAwareInterface
{
    public $id;
    public $agent_identifier;
    public $contact_id;
    public $start_date;
    public $end_date;
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
        $this->agent_identifier = (isset($data['agent_identifier'])) ? $data['agent_identifier'] : null;
        $this->contact_id = (isset($data['contact_id'])) ? $data['contact_id'] : null;
      	$this->start_date = (isset($data['start_date'])) ? $data['start_date'] : null;
      	$this->end_date = (isset($data['end_date'])) ? $data['end_date'] : null;
    }

    public function toArray()
    {
    	$data = array();
    	$data['id'] = (int) $this->id;
    	$data['agent_identifier'] =  $this->agent_identifier;
    	$data['contact_id'] = (int) $this->contact_id;
    	$data['start_date'] = ($this->start_date) ? $this->start_date : null;
    	$data['end_date'] = ($this->end_date) ? $this->end_date : null;
    	 
    	return $data;
    }

    public function checkIntegrity() {
    	$agent->agent_identifier = $data['agent_identifier'];
    	$agent->contact_id = $data['contact_id'];
    	$agent->start_date = $data['start_date'];
    	$agent->end_date = $data['end_date'];
    	 
    	$this->agent_identifier = trim(strip_tags($agent_identifier));
    
    	if (strlen($this->agent_identifier) > 255 ||
    		!is_int($this->contact_id) ||
    		!is_valid_date($this->start_date) ||
    		!is_valid_date($this->end_date)) {
    					
    		throw new \Exception('View error');
    	}
    }
    
    // Add content to this method:
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();
            
            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }

    public static function getTable()
    {
    	if (!Agent::$table) {
    		$sm = Context::getCurrent()->getServiceManager();
    		Agent::$table = $sm->get('PpitMasterData\Model\AgentTable');
    	}
    	return Agent::$table;
    }
}
