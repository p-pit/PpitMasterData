<?php 
namespace PpitMasterData\Controller;

use PpitCore\Model\Context;
use Sejour\Model\Sejour;
use Zend\Mvc\Controller\AbstractRestfulController; 
use Zend\View\Model\JsonModel;
 
class ProductRestController extends AbstractRestfulController
{
	
	public function getList()
    {
    	$filter = $this->params()->fromQuery('filter', NULL);
    	$select = Product::getTable()->getSelect();
    	$select->where->like('caption', $filter.'%');
    	$select->order(array('caption'));
    	$cursor = Product::getTable()->selectWith($select);
	    $data = array();
	    foreach($cursor as $product) {
	        $data[] = $product;
	    }
	    return new JsonModel(array('data' => $data));
    }
 
    public function get($id)
    {
    	$product = Product::getTable()->get($id);
    	return new JsonModel(array("data" => $product));
    }
 
    public function create($data)
    {
	    
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
