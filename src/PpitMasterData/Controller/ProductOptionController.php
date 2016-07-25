<?php
namespace PpitMasterData\Controller;

use DateInterval;
use Date;
use Zend\View\Model\ViewModel;
use PpitCore\Model\Csrf;
use PpitCore\Model\Context;
use PpitCore\Form\CsrfForm;
use PpitMasterData\Model\Product;
use PpitMasterData\Model\ProductOption;
use PpitMasterData\Model\ProductOptionMatrix;
use DOMPDFModule\View\Model\PdfModel;
use Zend\Session\Container;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\db\sql\Where;
use Zend\Mvc\Controller\AbstractActionController;

class ProductOptionController extends AbstractActionController
{
    public function indexAction()
    {
    	// Check that a product has been given
    	$product_id = (int) $this->params()->fromRoute('product_id', 0);
    	if (!$product_id) {
    		return $this->redirect()->toRoute('index');
    	}
    	// Retrieve the context
    	$context = Context::getCurrent();
    	
    	// Retrieve the product
    	$product = Product::getTable()->get($product_id);
    	
    	// Prepare the SQL request
    	$major = $this->params()->fromQuery('major', NULL);
    	if (!$major) $major = 'caption';
    	$dir = $this->params()->fromQuery('dir', NULL);
    	if (!$dir) $dir = 'ASC';
    	$adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
    	$select = ProductOption::getTable()->getSelect()
    		->where(array('product_id' => $product_id))
	    	->order(array($major.' '.$dir, 'caption'));
    	$cursor = ProductOption::getTable()->selectWith($select);

    	// Retrieve the options
    	$select = ProductOption::getTable()->getSelect()
	    	->where(array('product_id' => $product->id))
    		->order(array('id'));
    	$cursor = ProductOption::getTable()->selectWith($select);
    	$options = array();
    	foreach ($cursor as $option) $options[] = $option;
    	
    	// Generate the constraint matrix
    	$select = ProductOption::getTable()->getSelect()
	    	->where(array('product_id' => $product_id))
    		->order(array('id'));
    	$cursor = ProductOption::getTable()->selectWith($select);
    	$options = array();
    	foreach ($cursor as $option) $options[] = $option;
    	$matrix = array();
    	foreach ($options as $row) {
    		$matrix[$row->id] = array();
    		foreach ($options as $cell) $matrix[$row->id][$cell->id] = 0;
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
    			$productOptionMatrix = new ProductOptionMatrix();
    	
    			// Delete the previous constraints
    			ProductOptionMatrix::getTable()->multipleDelete(array('product_id' => $product_id));
    	
    			// Retrieve and save the new dependence constraints from the form
    			$productOptionMatrix->product_id = $product_id;
    			foreach ($matrix as $row_id => $row) {
    				foreach ($row as $col_id => $constraint) {
    					if ($row_id != $col_id && $request->getPost('dep_'.$row_id.'_'.$col_id) == 1) {
    						$productOptionMatrix->id = NULL;
    						$productOptionMatrix->row_option_id = $row_id;
    						$productOptionMatrix->col_option_id = $col_id;
    						$productOptionMatrix->constraint = 1; // 1 for dependence
    						ProductOptionMatrix::getTable()->save($productOptionMatrix);
    					}
    				}
    			}
    			// Retrieve and save the new exclusion constraints from the form
    			foreach ($matrix as $row_id => $row) {
    				foreach ($row as $col_id => $constraint) {
    					if ($row_id != $col_id && $request->getPost('exc_'.$row_id.'_'.$col_id) == 1) {
			    			$productOptionMatrix->id = NULL;
    						$productOptionMatrix->row_option_id = $row_id;
    						$productOptionMatrix->col_option_id = $col_id;
    						$productOptionMatrix->constraint = 2; // 2 for exclusion
    						ProductOptionMatrix::getTable()->save($productOptionMatrix);
    					}
    				}
    			}
				$message = 'OK';
    		}
    	}
    	// Retrieve the previous constraints
    	$select = ProductOptionMatrix::getTable()->getSelect()
    	->where(array('product_id' => $product_id));
    	$cursor = ProductOptionMatrix::getTable()->selectWith($select);
    	foreach ($cursor as $cell) $matrix[$cell->row_option_id][$cell->col_option_id] = $cell->constraint;

    	$view = new ViewModel(array(
    			'context' => $context,
				'config' => $context->getconfig(),
    			'major' => $major,
    			'dir' => $dir,
    			'product_id' => $product_id,
    			'product' => $product,
    			'productOptions' => $options,
    			'productOptionTable' => ProductOption::getTable(),
    			'csrfForm' => $csrfForm,
    			'message' => $message,
    			'error' => $error,
//    			'product_category_id' => $product_category_id,
    			'matrix' => $matrix,
    	));
   		if ($context->isSpaMode()) $view->setTerminal(true);
   		return $view;
    }
/*
    public function addAction()
    {
        // Check that a product has been given
    	$id = (int) $this->params()->fromRoute('id', 0);
    	if (!$id) {
    		return $this->redirect()->toRoute('index');
    	}
    	// Retrieve the context
    	$context = new COntext;

    	// Retrieve the product
    	$product = Product::getTable()->get($id);

    	$productOption = new ProductOption();
    	$productOption->product_id = $id;
    	$productOption->is_available = true;
    	 
    	$csrfForm = new CsrfForm();
    	$csrfForm->addCsrfElement('csrf');
    	$error = null;
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    		$csrfForm->setInputFilter((new Csrf('csrf'))->getInputFilter());
    		$csrfForm->setData($request->getPost());
    
    		if ($csrfForm->isValid()) { // CSRF check
    			$productOption->reference = $request->getPost('reference');
    			$productOption->caption = $request->getPost('caption');
    			$productOption->description = $request->getPost('description');
    			$productOption->is_available = $request->getPost('is_available');
    			$productOption->checkIntegrity();
    			 
    			// Check for duplicate data
    			$select = ProductOption::getTable()->getSelect()
	    			->where(array('product_id' => $product->id, 'reference' => $productOption->reference));
    			$cursor = ProductOption::getTable()->selectWith($select);
    			if (count($cursor) > 0) $error = 'Duplicate';
    			else {
    				ProductOption::getTable()->save($productOption);
    
    				// Redirect
    				return $this->redirect()->toRoute('productOption/index', array('id' => $product->id));
    			}
    		}
    	}
    	return array(
    			'context' => $context,
				'config' => $context->getconfig(),
    			'id' => $id,
    			'product' => $product,
    			'productOption' => $productOption,
    			'csrfForm' => $csrfForm,
    			'error' => $error
    	);
    }*/
    
    public function updateAction()
    {
    	$product_id = (int) $this->params()->fromRoute('product_id', 0);
    	if (!$product_id) {
    		return $this->redirect()->toRoute('index');
    	}
    	// Retrieve the context
    	$context = Context::getCurrent();

    	// Retrieve or create the option
    	$id = (int) $this->params()->fromRoute('id', 0);
    	if ($id) $productOption = ProductOption::get($id);
    	else $productOption = ProductOption::instanciate($product_id);
    
    	$csrfForm = new CsrfForm();
    	$csrfForm->addCsrfElement('csrf');
    	$message = null;
    	$error = null;
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    		$csrfForm->setInputFilter((new Csrf('csrf'))->getInputFilter());
    		$csrfForm->setData($request->getPost());
    		
    		if ($csrfForm->isValid()) { // CSRF check
    		    $return = $productOption->loadDataFromRequest($request);

				if ($return != 'OK') $error = $return;
    			else {
    			
	    			// Atomicity
	    			$connection = ProductOption::getTable()->getAdapter()->getDriver()->getConnection();
	    			$connection->beginTransaction();
	    			try {
			    		// Add or update
	    				if (!$id) $return = $productOption->add();
	    				else $return = $productOption->update($request->getPost('update_time'));
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
    			'product_id' => $product_id,
    			'id' => $id,
    			'productOption' => $productOption,
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
    	if (!$id) {
    		return $this->redirect()->toRoute('index');
    	}
    	// Retrieve the current user
    	$context = Context::getCurrent();
    
    	// Retrieve the data to delete
    	$productOption = ProductOption::getTable()->get($id);

    	// Retrieve the product
    	$product = Product::getTable()->get($productOption->product_id);
    	 
    	$csrfForm = new CsrfForm();
    	$csrfForm->addCsrfElement('csrf');
    	$message = null;
    	$error = null;
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    
    		$csrfForm->setInputFilter((new Csrf('csrf'))->getInputFilter());
    		$csrfForm->setData($request->getPost());
    
    		if ($csrfForm->isValid()) {
    	   
    			// Delete the row
    			ProductOption::getTable()->delete($id);
    			 
				$message = 'OK';
    		}
    	}
    	$view = new ViewModel(array(
    			'context' => $context,
				'config' => $context->getconfig(),
    			'product' => $product,
    			'id' => $id,
    			'productOption' => $productOption,
    			'csrfForm' => $csrfForm,
    			'message' => $message,
    			'error' => $error
    	));
   		if ($context->isSpaMode()) $view->setTerminal(true);
   		return $view;
    }
}
