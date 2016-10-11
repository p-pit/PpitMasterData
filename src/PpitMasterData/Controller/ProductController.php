<?php
namespace PpitMasterData\Controller;

use PpitContact\Model\Vcard;
use PpitCore\Model\Context;
use PpitMasterData\Model\Product;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class ProductController extends AbstractActionController
{
    public function indexAction()
    {
    	$context = Context::getCurrent();
		if (!$context->isAuthenticated()) $this->redirect()->toRoute('home');

		// Retrieve the type
		$type = $this->params()->fromRoute('type', 0);

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
    
    public function criteriaAction()
    {
    	$context = Context::getCurrent();

    	$instance_caption = $this->params()->fromRoute('instance_caption', null);
    	$type = $this->params()->fromRoute('type', null);
    	 
    	$safe = $context->getConfig()['ppitUserSettings']['safe'];
    	$safeEntry = $safe[$instance_caption];
    	$username = null;
    	$password = null;
    
    	// Check basic authentication
    	if (isset($_SERVER['PHP_AUTH_USER'])) {
    		$username = $_SERVER['PHP_AUTH_USER'];
    		$password = $_SERVER['PHP_AUTH_PW'];
    	} elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    		if (strpos(strtolower($_SERVER['HTTP_AUTHORIZATION']),'basic')===0)
    			list($username, $password) = explode(':',base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
    	}
    	if (!array_key_exists($username, $safeEntry) || $password != $safeEntry[$username]) {
    		 
    		// Write to the log
    		$logger->info('product/criteria/'.$instance_caption.'/'.$type.';401;'.$username);
    		$this->getResponse()->setStatusCode('401');
    		return $this->getResponse();
    	}
    	else {
    		return new JsonModel($context->getConfig('ppitProduct'.(($type) ? '/'.$type : ''))['criteria']);
    	}
    }
    
	public function getFilters($params, $type = null)
	{
		// Retrieve the context
		$context = Context::getCurrent();
		
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
		
		foreach ($context->getConfig('ppitProduct'.(($type) ? '/'.$type : ''))['criteria'] as $criterion => $unused) {
			$value = ($params()->fromQuery($criterion, null));
			if ($value) $filters[$criterion] = $value;
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

		// Retrieve the order type
		$type = $this->params()->fromRoute('type', null);

		$params = $this->getFilters($this->params(), $type);

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

   	public function restListAction()
   	{
   		$context = Context::getCurrent();
   	
   		$instance_caption = $this->params()->fromRoute('instance_caption', null);
   		$type = $this->params()->fromRoute('type', null);
   	
   		$safe = $context->getConfig()['ppitUserSettings']['safe'];
   		$safeEntry = $safe[$instance_caption];
   		$username = null;
   		$password = null;
   	
   		// Check basic authentication
   		if (isset($_SERVER['PHP_AUTH_USER'])) {
   			$username = $_SERVER['PHP_AUTH_USER'];
   			$password = $_SERVER['PHP_AUTH_PW'];
   		} elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
   			if (strpos(strtolower($_SERVER['HTTP_AUTHORIZATION']),'basic')===0)
   				list($username, $password) = explode(':',base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
   		}
   		if (!array_key_exists($username, $safeEntry) || $password != $safeEntry[$username]) {
   			 
   			// Write to the log
   			$logger->info('product/restList/'.$instance_caption.'/'.$type.';401;'.$username);
   			$this->getResponse()->setStatusCode('401');
   			return $this->getResponse();
   		}
   		else {
   			return new JsonModel($this->getList()->products);
   		}
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
