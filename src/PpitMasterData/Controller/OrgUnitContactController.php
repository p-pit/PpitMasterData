<?php

namespace PpitMasterData\Controller;

use PpitCore\Model\Csrf;
use PpitCore\Model\Context;
use PpitCore\Form\CsrfForm;
use PpitMasterData\Model\OrgUnit;
use PpitMasterData\Model\OrgUnitContact;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class OrgUnitContactController extends AbstractActionController
{
    public function listAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();

    	// Retrieve the organizational unit
    	$org_unit_id = $this->params()->fromRoute('org_unit_id', 0);
    	if (!$org_unit_id) return $this->redirect()->toRoute('index');
    	else $orgUnit = OrgUnit::get($org_unit_id);
    	 
		// Ordering
    	$major = $this->params()->fromQuery('major', NULL);
    	if (!$major) $major = 'n_fn';
    	$dir = $this->params()->fromQuery('dir', NULL);
    	if (!$dir) $dir = 'ASC';
    
		$orgUnitContacts = OrgUnitContact::getList($major, $dir, $org_unit_id);
    
    	$view = new ViewModel(array(
    			'context' => $context,
    			'config' => $context->getconfig(),
    			'major' => $major,
    			'dir' => $dir,
    			'orgUnit' => $orgUnit,
    			'orgUnitContacts' => $orgUnitContacts,
    	));
    	$view->setTerminal(true);
    	return $view;
    }

    public function addAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();
    	 
    	$org_unit_id = (int) $this->params()->fromRoute('org_unit_id', 0);
    	if (!$org_unit_id) $this->redirect()->toRoute('index');
    	else $orgUnit = OrgUnit::get($org_unit_id);

    	$id = (int) $this->params()->fromRoute('id', 0);
		if ($id) $orgUnitContact = OrgUnitContact::get($id);
    	$orgUnitContact = OrgUnitContact::instanciate($org_unit_id);
    	
    	$csrfForm = new CsrfForm();
    	$csrfForm->addCsrfElement('csrf');
    	$message = null;
    	$error = null;
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    		$csrfForm->setInputFilter((new Csrf('csrf'))->getInputFilter());
    		$csrfForm->setData($request->getPost());
    
    		if ($csrfForm->isValid()) { // CSRF check
    			$return = $orgUnitContact->loadDataFromRequest($request);

				if ($return != 'OK') $error = $return;
    			else {
    			
	    			// Atomicity
	    			$connection = OrgUnitContact::getTable()->getAdapter()->getDriver()->getConnection();
	    			$connection->beginTransaction();
	    			try {
			    		// Add 
	    				$return = $orgUnitContact->add();
	   					if ($return != 'OK') {
		    				$connection->rollback();
							$error = $return;
						}
						else {
	    					$id = $orgUnitContact->id;
							$orgUnitContact = OrgUnitContact::get($orgUnitContact->id);
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
    			'orgUnitContact' => $orgUnitContact,
    			'org_unit_id' => $org_unit_id,
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
		$orgUnitContact = OrgUnitContact::get($id);
    	
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
    			$connection = OrgUnitContact::getTable()->getAdapter()->getDriver()->getConnection();
    			$connection->beginTransaction();
    			try {
		    		// Delete the row
					$return = $orgUnitContact->delete($request->getPost('update_time'));
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
    		'orgUnitContact' => $orgUnitContact,
    		'id' => $id,
    		'csrfForm' => $csrfForm,
    		'message' => $message,
    		'error' => $error,
    	));
   		$view->setTerminal(true);
   		return $view;
    }
}
    