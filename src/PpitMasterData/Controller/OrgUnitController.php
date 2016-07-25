<?php

namespace PpitMasterData\Controller;

use PpitCore\Model\Csrf;
use PpitCore\Model\Context;
use PpitCore\Form\CsrfForm;
use PpitMasterData\Model\OrgUnit;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class OrgUnitController extends AbstractActionController
{
    public function indexAction()
    {    	
    	// Retrieve the context
    	$context = Context::getCurrent();

    	$view = new ViewModel(array(
    			'context' => $context,
				'config' => $context->getconfig(),
    	));
   		if ($context->isSpaMode()) $view->setTerminal(true);
   		return $view;
    }

    public function listAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();
    
    	// Filter
		$filter = array();
    	$type = $this->params()->fromQuery('type', NULL);
    	if ($type) $filter['type'] = $type;
    	$name = $this->params()->fromQuery('name', NULL);
    	if ($name) $filter['caption'] = $name;

		// Order
    	$major = $this->params()->fromQuery('major', NULL);
    	if (!$major) $major = 'caption';
    	$dir = $this->params()->fromQuery('dir', NULL);
    	if (!$dir) $dir = 'ASC';
    
		$orgUnits = OrgUnit::getList($major, $dir, $filter);
    
    	$view = new ViewModel(array(
    			'context' => $context,
    			'config' => $context->getconfig(),
    			'type' => $type,
    			'name' => $name,
    			'major' => $major,
    			'dir' => $dir,
    			'orgUnits' => $orgUnits,
    	));
    	$view->setTerminal(true);
    	return $view;
    }

    public function dataListAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();
    
    	// Filter
    	$filter = array();
    	$type = $this->params()->fromQuery('type', NULL);
    	if ($type) $filter['type'] = $type;
    	$name = $this->params()->fromQuery('name', NULL);
    	if ($name) $filter['caption'] = $name;
    
    	// Order
    	$major = $this->params()->fromQuery('major', NULL);
    	if (!$major) $major = 'caption';
    	$dir = $this->params()->fromQuery('dir', NULL);
    	if (!$dir) $dir = 'ASC';
    
    	$orgUnits = OrgUnit::getList($major, $dir, $filter);
    	$data = array();
    	foreach ($orgUnits as $orgUnit) {
    		$data[] = array(
    				'id' => $orgUnit->id,
    				'identifier' => $orgUnit->identifier,
    				'caption' => $orgUnit->caption,
    		);
    	}
    	return new JsonModel(array('data' => $data));
    }
    
    public function updateAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();

    	$id = (int) $this->params()->fromRoute('id', 0);
    	if ($id) $orgUnit = OrgUnit::get($id);
    	else {
    		$parent_id = (int) $this->params()->fromRoute('parent_id', 0);
    		$orgUnit = OrgUnit::instanciate($parent_id);
    	}
    	$csrfForm = new CsrfForm();
    	$csrfForm->addCsrfElement('csrf');
    	$message = null;
    	$error = null;
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    		$csrfForm->setInputFilter((new Csrf('csrf'))->getInputFilter());
    		$csrfForm->setData($request->getPost());
    
    		if ($csrfForm->isValid()) { // CSRF check
    			$return = $orgUnit->loadDataFromRequest($request);

				if ($return != 'OK') $error = $return;
    			else {
    			
	    			// Atomicity
	    			$connection = OrgUnit::getTable()->getAdapter()->getDriver()->getConnection();
	    			$connection->beginTransaction();
	    			try {
			    		// Add or update
	    				if (!$id) $return = $orgUnit->add();
	    				else $return = $orgUnit->update($request->getPost('update_time'));
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
    	}
    	$view = new ViewModel(array(
    			'context' => $context,
				'config' => $context->getconfig(),
    			'orgUnit' => $orgUnit,
       			'id' => $id,
    			'csrfForm' => $csrfForm,
    			'message' => $message,
    			'error' => $error
    	));
   		if ($context->isSpaMode()) $view->setTerminal(true);
   		return $view;
    }
    
	public function deleteAction()
    {
    	$id = (int) $this->params()->fromRoute('id', 0);
    	if (!$id) return $this->redirect()->toRoute('index');

    	// Retrieve the context
    	$context = Context::getCurrent();

    	// Retrieve the organizational unit
		$orgUnit = OrgUnit::get($id);
    	
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
    			$connection = OrgUnit::getTable()->getAdapter()->getDriver()->getConnection();
    			$connection->beginTransaction();
    			try {
		    		// Delete the row
					$return = $orgUnit->delete($request->getPost('update_time'));
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
    		'orgUnit' => $orgUnit,
    		'id' => $id,
    		'csrfForm' => $csrfForm,
    		'message' => $message,
    		'error' => $error,
    	));
   		if ($context->isSpaMode()) $view->setTerminal(true);
   		return $view;
    }
}
    