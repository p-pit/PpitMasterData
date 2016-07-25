<?php 
namespace PpitMasterData\Controller;

use PpitCore\Model\Context;
use PpitMasterData\Model\Agent;
use Zend\Mvc\Controller\AbstractRestfulController; 
use Zend\View\Model\JsonModel;

class AgentRestController extends AbstractRestfulController
{
	public function getList()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();
    	
    	$select = Agent::getTable()->getSelect();
    	$cursor = Agent::getTable()->selectWith($select);
	    $data = array();
	    foreach($cursor as $agent) {
	        $data[] = $agent;
	    }
	    return new JsonModel(array('data' => $data));
    }
 
    public function get($id)
    {
    	// Retrieve the context
    	$context = Context::getCurrent();
    	
    	$agent = Agent::getTable()->get($id);
	    return new JsonModel(array('data' => $agent));
    }
 
    public function create($data)
    {
    	// Retrieve the context
    	$context = Context::getCurrent();

    	$agent = new Agent();

    	// retrieve the values
    	$agent->id = 0;
    	$agent->agent_identifier = $data['agent_identifier'];
    	$agent->contact_id = $data['contact_id'];
    	$agent->start_date = $data['start_date'];
    	$agent->end_date = $data['end_date'];
    	 
    	$agent->checkIntegrity();
   
    	$id = Agent::getTable()->save($agent);

    	return new JsonModel(array(
    		'id' => $id,
    	));
    }
 
    public function update($id, $data)
    {
    	
    	# code...
    }
 
    public function delete($id)
    {
    	
    	# code...
    }
}