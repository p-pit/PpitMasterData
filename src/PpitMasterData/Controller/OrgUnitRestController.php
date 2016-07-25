<?php 
namespace PpitMasterData\Controller;

use PpitCore\Model\Context;
use PpitMasterData\Model\OrgUnit;
use Zend\Mvc\Controller\AbstractRestfulController; 
use Zend\View\Model\JsonModel;

class OrgUnitRestController extends AbstractRestfulController
{
	public function getList()
    {
    	// Retrieve the current user
    	$context = Context::getCurrent();
    	
    	$select = OrgUnit::getTable()->getSelect();
    	$cursor = OrgUnit::getTable()->selectWith($select);
	    $data = array();
	    foreach($cursor as $orgUnit) {
	        $data[] = $orgUnit;
	    }
	    return new JsonModel(array('data' => $data));
    }
 
    public function get($id)
    {
    	// Retrieve the current user
    	$context = Context::getCurrent();
    	
    	$orgUnit = OrgUnit::getTable()->get($id);
	    return new JsonModel(array('data' => $orgUnit));
    }
 
    public function create($data)
    {
    	// Retrieve the current user
    	$context = Context::getCurrent();
    	$orgUnit = new OrgUnit();
    	
    	// retrieve the values
    	$orgUnit->id = 0;
    	$orgUnit->type = $data['type'];
    	$orgUnit->parent_id = $data['parent_id'];
    	$orgUnit->identifier = $data['identifier'];
    	$orgUnit->caption = $data['caption'];
    	$orgUnit->description = $data['description'];
    	$orgUnit->place_of_business_id = $data['place_of_business_id'];

    	$orgUnit->checkIntegrity();
    	
		// Throw an exception if a parent organisational unit is given and does not exist
    	if ($parent_id != 0) OrgUnit::getTable()->get($parent_id);
		
		// Throw an exception if a place of business is given and does not exist
    	if ($place_of_business_id != 0) PlaceOfBusiness::getTable()->get($place_of_business_id); 

    	$orgUnit->id = OrgUnit::getTable()->save($orgUnit);

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