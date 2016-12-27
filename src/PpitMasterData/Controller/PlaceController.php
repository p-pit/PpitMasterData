<?php
namespace PpitMasterData\Controller;

use DateInterval;
use Date;
use PpitCore\Model\Community;
use PpitCore\Model\Csrf;
use PpitCore\Model\Context;
use PpitCore\Model\Link;
use PpitCore\Model\Place;
use PpitCore\Form\CsrfForm;
use Zend\db\sql\Where;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class PlaceController extends AbstractActionController
{
	public function indexAction()
	{
		// Retrieve the context
		$context = Context::getCurrent();
	
		$view = new ViewModel(array(
				'context' => $context,
				'config' => $context->getconfig(),
		));
		$view->setTerminal(true);
		return $view;
	}
	
	public function listAction()
	{
		// Retrieve the context
		$context = Context::getCurrent();
		$instance_id = $context->getInstanceId();

		// Filter
		$filter = array();
		$name = $this->params()->fromQuery('name', NULL);
		if ($name) $filter['name'] = $name;

		// Order
		$major = $this->params()->fromQuery('major', NULL);
		if (!$major) $major = 'name';
		$dir = $this->params()->fromQuery('dir', NULL);
		if (!$dir) $dir = 'ASC';
	    
		$places = Place::getList($major, $dir, $filter);
	    
		$view = new ViewModel(array(
				'context' => $context,
				'config' => $context->getconfig(),
				'name' => $name,
				'major' => $major,
				'dir' => $dir,
				'places' => $places,
		));
		$view->setTerminal(true);
		return $view;
	}

	public function dataListAction()
	{
		// Retrieve the context
		$context = Context::getCurrent();
		$instance_id = $context->getInstanceId();

		// Filter
		$filter = array();
		$identifier = $this->params()->fromQuery('identifier', NULL);
		if ($identifier) $filter['identifier'] = $identifier;
	
		// Order
		$major = $this->params()->fromQuery('major', NULL);
		if (!$major) $major = 'name';
		$dir = $this->params()->fromQuery('dir', NULL);
		if (!$dir) $dir = 'ASC';

		$places = Place::getList($major, $dir, $filter);
		$data = array();
		foreach ($places as $place) {
			$data[] = array(
					'id' => $place->id,
					'identifier' => $place->identifier,
					'name' => $place->name,
			);
		}
    	return new JsonModel(array('data' => $data));
	}
	
    public function updateAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();

    	$id = (int) $this->params()->fromRoute('id', 0);
    	if ($id) $place = Place::get($id);
    	else $place = Place::instanciate();
    	
    	$csrfForm = new CsrfForm();
    	$csrfForm->addCsrfElement('csrf');
    	$message = null;
    	$error = null;
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    		$csrfForm->setInputFilter((new Csrf('csrf'))->getInputFilter());
    		$csrfForm->setData($request->getPost());
    
    		if ($csrfForm->isValid()) { // CSRF check
    			$place->loadDataFromRequest($request);
    			
    			// Atomicity
    			$connection = Place::getTable()->getAdapter()->getDriver()->getConnection();
    			$connection->beginTransaction();
    			try {
		    		// Add or update
    				if (!$id) $return = $place->add();
    				else $return = $place->update($request->getPost('update_time'));
   					if ($return != 'OK') {
	    				$connection->rollback();
						$error = $return;
					}
					else {
						$connection->commit();
						$message = $return;
					}
    			}
           	    catch (\Exception $e) {
	    			$connection->rollback();
	    			throw $e;
	    		}
    		}
    	}
    	$view = new ViewModel(array(
    			'context' => $context,
				'config' => $context->getconfig(),
    			'place' => $place,
       			'id' => $id,
    			'csrfForm' => $csrfForm,
    			'message' => $message,
    			'error' => $error
    	));
   		$view->setTerminal(true);
   		return $view;
    }
    
	public function deleteAction()
    {
    	$id = (int) $this->params()->fromRoute('id', 0);
    	if (!$id) return $this->redirect()->toRoute('index');

    	// Retrieve the context
    	$context = Context::getCurrent();

    	// Retrieve the organizational unit
		$place = Place::get($id);
    	
		$csrfForm = new CsrfForm();
		$csrfForm->addCsrfElement('csrf');
		$message = null;
		$error = null;
    	// Retrieve the user validation from the post
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    		
    		$csrfForm->setInputFilter((new Csrf('csrf'))->getInputFilter());
    		$csrfForm->setData($request->getPost());
    		
    		if ($csrfForm->isValid()) {

    			// Atomicity
    			$connection = Place::getTable()->getAdapter()->getDriver()->getConnection();
    			$connection->beginTransaction();
    			try {
		    		// Delete the row
					$return = $place->delete($request->getPost('update_time'));
					if ($return != 'OK') {
						$connection->rollback();
						$error = $return;
					}
					else {
						$connection->commit();
						$message = $return;
					}
    			}
           	    catch (\Exception $e) {
	    			$connection->rollback();
	    			throw $e;
	    		}
    		}  
    	}
    	$view = new ViewModel(array(
    		'context' => $context,
			'config' => $context->getconfig(),
    		'place' => $place,
    		'id' => $id,
    		'csrfForm' => $csrfForm,
    		'message' => $message,
    		'error' => $error,
    	));
   		$view->setTerminal(true);
   		return $view;
    }
}
