<?php
namespace PpitMasterData\Controller;

use Zend\View\Model\ViewModel;
use PpitCore\Model\Csrf;
use PpitCore\Model\Context;
use PpitCore\Form\CsrfForm;
use PpitMasterData\Model\ProductCategory;
use PpitMasterData\Form\ProductCategoryForm;
use Zend\Session\Container;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;

class ProductCategoryController extends AbstractActionController
{
   	public function indexAction()
    {
    	// Retrieve the current user
    	$context = Context::getCurrent();
    	
    	// Prepare the SQL request
    	$major = $this->params()->fromQuery('major', NULL);
    	if (!$major) $major = 'caption';
    	$dir = $this->params()->fromQuery('dir', NULL);
    	if (!$dir) $dir = 'ASC';

    	$select = ProductCategory::getTable()->getSelect();
    	$select->order(array($major.' '.$dir, 'caption'));

    	// Execute the request
    	$cursor = ProductCategory::getTable()->selectWith($select);
    	$productCategories = array();
    	foreach ($cursor as $productCategory) $productCategories[] = $productCategory;

    	// Return the link list
    	$view = new ViewModel(array(
    		'context' => $context,
			'config' => $context->getconfig(),
    		'major' => $major,
    		'dir' => $dir,
    		'productCategories' => $productCategories,
        ));
   		$view->setTerminal(true);
   		return $view;
    }

    public function updateAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();

    	// Check the presence of an id parameter (update case) or not (add case)
    	$id = (int) $this->params()->fromRoute('id', 0);
    	if ($id) $productCategory = ProductCategory::getTable()->get($id);
    	else $productCategory = ProductCategory::instanciate();
    	 
    	$csrfForm = new CsrfForm();
    	$csrfForm->addCsrfElement('csrf');
    	$error = null;
    	$message = null;
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    		$csrfForm->setInputFilter((new Csrf('csrf'))->getInputFilter());
    		$csrfForm->setData($request->getPost());
    
    		if ($csrfForm->isValid()) { // CSRF check
    		    $return = $productCategory->loadDataFromRequest($request);

    		    // Atomicity
    		    $connection = ProductCategory::getTable()->getAdapter()->getDriver()->getConnection();
    		    $connection->beginTransaction();
    		    try {
    		    	// Add or update
    		    	if (!$id) $return = $productCategory->add();
    		    	else $return = $productCategory->update($request->getPost('update_time'));
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
    			'id' => $id,
    			'productCategory' => $productCategory,
    			'context' => $context,
				'config' => $context->getconfig(),
    			'csrfForm' => $csrfForm,
    			'message' => $message,
    			'error' => $error,
    	));
   		$view->setTerminal(true);
   		return $view;
    }
    
    public function deleteAction()
    {
		// Check the presence of the id parameter for the entity to delete
    	$id = (int) $this->params()->fromRoute('id', 0);
    	if (!$id) {
    		return $this->redirect()->toRoute('productCategory');
    	}
    	// Retrieve the current user
    	$context = Context::getCurrent();

    	// Retrieve the existing data
    	$productCategory = ProductCategory::getTable()->get($id);

    	$csrfForm = new CsrfForm();
    	$csrfForm->addCsrfElement('csrf');
		$message = null;
		$error = null;
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    		$csrfForm->setInputFilter((new Csrf('csrf'))->getInputFilter());
    		$csrfForm->setData($request->getPost());
    		
    		if ($csrfForm->isValid()) { // CSRF check

    			// Atomicity
    			$connection = ProductCategory::getTable()->getAdapter()->getDriver()->getConnection();
    			$connection->beginTransaction();
    			try {
    				// Delete the data
    				$return = $productCategory->delete($request->getPost('update_time'));
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
    		'productCategory' => $productCategory,
    		'id' => $id,
    		'csrfForm' => $csrfForm,
    		'message' => $message,
    		'error' => $error,
    	));
   		$view->setTerminal(true);
   		return $view;
    }
}
