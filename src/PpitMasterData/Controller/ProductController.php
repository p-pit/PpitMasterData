<?php
namespace PpitMasterData\Controller;

use PpitCore\Form\CsrfForm;
use PpitCore\Model\Context;
use PpitCore\Model\Csrf;
use PpitCore\Model\Place;
use PpitCore\Model\Vcard;
use PpitMasterData\Model\Product;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Log\Logger;
use Zend\Log\Writer;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class ProductController extends AbstractActionController
{
    public function indexAction()
    {
    	$context = Context::getCurrent();
		if (!$context->isAuthenticated()) $this->redirect()->toRoute('home');
    	$place = Place::getTable()->transGet($context->getPlaceId());

		$type = $this->params()->fromRoute('type', 'p-pit-studies');
		$types = Context::getCurrent()->getConfig('commitment/types')['modalities'];
		
		// Retrieve the type
		$type = $this->params()->fromRoute('type', 0);

		$menu = $context->getConfig('menu');
		
    	return new ViewModel(array(
    			'context' => $context,
    			'config' => $context->getConfig(),
    			'place' => $place,
				'type' => $type,
				'types' => $types,
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
		
		$def = $context->getConfig('ppitProduct'.(($type) ? '/'.$type : ''));
		if ($def) foreach ($def['criteria'] as $criterion => $unused) {
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

   	public function serviceListAction()
   	{
   		$context = Context::getCurrent();
   		$writer = new Writer\Stream('data/log/commitment_try.txt');
   		$logger = new Logger();
   		$logger->addWriter($writer);

   		$instance_caption = $context->getInstance()->caption;
   		$type = 'service';

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
   			$logger->info('product/serviceList/'.$instance_caption.'/'.$type.';401;'.$username);
   			$this->getResponse()->setStatusCode('401');
   			return $this->getResponse();
   		}
   		else {
   			$result = array();
			$params = $this->getFilters($this->params(), 'service');
//			$params['is_available'] = true; // Temporary
   			foreach (Product::getList('service', $params, 'reference', 'ASC', 'search') as $product) {
   				$item = array(
   						'reference' => $product->reference,
   						'caption' => $product->caption,
   						'description' => $product->description,
   				);
   				foreach ($context->getConfig('ppitProduct/service')['properties'] as $propertyId => $property) {
   					$item[$property['property_name']] = $product->toArray()[$propertyId];
   				}
   				$item['variants'] = $product->variants;
   				$result[] = $item;
   			}
   			return new JsonModel($result);
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

    public function updateAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();

    	$type = $this->params()->fromRoute('type', null);

    	$id = (int) $this->params()->fromRoute('id', 0);
    	if ($id) $product = Product::get($id);
    	else $product = Product::instanciate();

    	$action = $this->params()->fromRoute('act', null);

    	// Instanciate the csrf form
    	$csrfForm = new CsrfForm();
    	$csrfForm->addCsrfElement('csrf');
    	$error = null;
    	if ($action == 'delete') $message = 'confirm-delete';
    	elseif ($action) $message =  'confirm-update';
    	else $message = null;
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    		$message = null;
    		$csrfForm->setInputFilter((new Csrf('csrf'))->getInputFilter());
    		$csrfForm->setData($request->getPost());
    		 
    		if ($csrfForm->isValid()) { // CSRF check

    			// Load the input data
		    	$data = array();
    			$data['type'] = $request->getPost(('product-type'));
		    	$data['brand'] = $request->getPost(('product-brand'));
				$data['reference'] = $request->getPost(('product-reference'));
				$data['caption'] = $request->getPost(('product-caption'));
				$data['description'] = $request->getPost(('product-description'));
				$data['is_available'] = $request->getPost(('product-is_available'));
				$variantNumber = $request->getPost('product-variant-number');
				$data['variants'] = array();
				for ($i = 0; $i < $variantNumber; $i++) {
					$variant = array('price' => $request->getPost('product-price_'.$i));
					$data['variants'][] = $variant;
				}
				foreach ($context->getConfig('ppitProduct/update'.(($type) ? '/'.$type : '')) as $propertyId => $unused) {
					$data[$propertyId] = $request->getPost('product-'.$propertyId);
				}
				$data['tax_1_share'] = $request->getPost(('product-tax_1_share'))/100;
				$data['tax_2_share'] = $request->getPost(('product-tax_2_share'))/100;
				$data['tax_3_share'] = $request->getPost(('product-tax_3_share'))/100;
				if ($product->loadData($data) != 'OK') throw new \Exception('View error');
	    		// Atomically save
	    		$connection = Product::getTable()->getAdapter()->getDriver()->getConnection();
	    		$connection->beginTransaction();
	    		try {
	    			if (!$product->id) $rc = $product->add();
	    			elseif ($action == 'delete') {
	    				$product->status = 'deleted';
	    				$rc = $product->update($request->getPost('update_time'));
	    			}
	    			else {
    					$rc = $product->update($request->getPost('update_time'));
	    			}
    				if ($rc != 'OK') $error = $rc;
	    			if ($error) $connection->rollback();
	    			else {
	    				$connection->commit();
	    				$message = 'OK';
	    			}
	    		}
	    		catch (\Exception $e) {
	    			$connection->rollback();
	   				throw $e;
	   			}
	   			$action = null;
    		}
    	}
    
    	$view = new ViewModel(array(
    			'context' => $context,
    			'config' => $context->getconfig(),
    			'type' => $type,
    			'id' => $id,
    			'action' => $action,
    			'product' => $product,
    			'csrfForm' => $csrfForm,
    			'error' => $error,
    			'message' => $message
    	));
    	$view->setTerminal(true);
    	return $view;
    }
}
