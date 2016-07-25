<?php
namespace PpitMasterData\Model;

use PpitCore\Model\Context;
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
      	$this->effective_date = (isset($data['effective_date'])) ? $data['effective_date'] : null;
    }

    public function toArray()
    {
    	$data = array();
    	$data['id'] = (int) $this->id;
    	$data['agent_id'] =  (int) $this->agent_id;
    	$data['org_unit_id'] = (int) $this->org_unit_id;
    	$data['effective_date'] = ($this->effective_date) ? $this->effective_date : null;
    	 
    	return $data;
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
    		AgentAttachment::$table = $sm->get('PpitMasterData\Model\AgentAttachmentTable');
    	}
    	return AgentAttachment::$table;
    }
}
