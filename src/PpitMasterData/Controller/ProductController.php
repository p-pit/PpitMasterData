<?php
namespace PpitMasterData\Controller;

use PpitContact\Model\Vcard;
use PpitCore\Model\Context;
use PpitMasterData\Model\Product;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ProductController extends AbstractActionController
{
    public function indexAction()
    {
    	$context = Context::getCurrent();
		if (!$context->isAuthenticated()) $this->redirect()->toRoute('home');

		// Retrieve the type
		$type = $this->params()->fromRoute('type', 0);
/*
		$instance_id = $context->getInstanceId();
		$community_id = (int) $context->getCommunityId();
		$contact = Vcard::getNew($instance_id, $community_id);*/

		$menu = $context->getConfig('menu');
		
    	return new ViewModel(array(
    			'context' => $context,
    			'config' => $context->getConfig(),
//    			'community_id' => $community_id,
				'type' => $type,
    			'menu' => $menu,
//    			'contact' => $contact,
    	));
    }
	public function getFilters($params)
	{
		// Retrieve the query parameters
		$filters = array();

		$brand = ($params()->fromQuery('brand', null));
		if ($brand) $filters['brand'] = $brand;

		$reference = ($params()->fromQuery('reference', null));
		if ($reference) $filters['reference'] = $reference;

		$caption = ($params()->fromQuery('caption', null));
		if ($caption) $filters['caption'] = $caption;
		
		$min_price = ($params()->fromQuery('min_price', null));
		if ($min_price) $filters['min_price'] = $min_price;
		
		$max_price = ($params()->fromQuery('max_price', null));
		if ($max_price) $filters['max_price'] = $max_price;

		for ($i = 1; $i < 20; $i++) {
		
			$property = ($params()->fromQuery('property_'.$i, null));
			if ($property) $filters['property_'.$i] = $property;
			$min_property = ($params()->fromQuery('min_property_'.$i, null));
			if ($min_property) $filters['min_property_'.$i] = $min_property;
			$max_property = ($params()->fromQuery('max_property_'.$i, null));
			if ($max_property) $filters['max_property_'.$i] = $max_property;
		}
		
		return $filters;
	}
	
   	public function searchAction()
   	{
		// Retrieve the context
		$context = Context::getCurrent();

		// Retrieve the type
		$type = $this->params()->fromRoute('type', 0);

   		// Return the link list
   		$view = new ViewModel(array(
   				'context' => $context,
				'config' => $context->getconfig(),
   				'type' => $type,
   		));
		$view->setTerminal(true);
       	return $view;
   	}

   	public function getList()
   	{
		// Retrieve the context
		$context = Context::getCurrent();

		$params = $this->getFilters($this->params());

		// Retrieve the order type
		$type = $this->params()->fromRoute('type', null);
		
		$major = ($this->params()->fromQuery('major', 'caption'));
		$dir = ($this->params()->fromQuery('dir', 'ASC'));

		if (count($params) == 0) $mode = 'todo'; else $mode = 'search';

		// Retrieve the list
		$products = Product::getList($type, $params, $major, $dir, $mode);

   		// Return the link list
   		$view = new ViewModel(array(
   				'context' => $context,
				'config' => $context->getconfig(),
   				'type' => $type,
   				'products' => $products,
   				'mode' => $mode,
   				'params' => $params,
   				'major' => $major,
   				'dir' => $dir,
   		));
		$view->setTerminal(true);
       	return $view;
   	}
   	
   	public function listAction()
   	{
   		return $this->getList();
   	}

   	public function exportAction()
   	{
   		return $this->getList();
   	}
    
    public function detailAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();
    	
    	// Retrieve the type
		$type = $this->params()->fromRoute('type', 0);
    	
		$id = (int) $this->params()->fromRoute('id', 0);
    	if ($id) $product = Product::get($id);
    	else $product = Product::instanciate($type);

    	$view = new ViewModel(array(
    		'context' => $context,
			'config' => $context->getconfig(),
    		'id' => $product->id,
    		'product' => $product,
    	));
		$view->setTerminal(true);
		return $view;
    }
}
