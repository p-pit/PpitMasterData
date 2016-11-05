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

    public function getList()
    {
    	$context = Context::getCurrent();
    	$type = $this->params()->fromRoute('type', null);
    	$product_id = (int) $this->params()->fromRoute('product_id', 0);
    	$params = array('product_id' => $product_id);
    	$major = ($this->params()->fromQuery('major', 'caption'));
    	$dir = ($this->params()->fromQuery('dir', 'ASC'));
    	$mode = 'search';
    
    	// Retrieve the list
    	$options = ProductOption::getList($type, $params, $major, $dir, $mode);
    
    	// Return the link list
    	$view = new ViewModel(array(
    			'context' => $context,
    			'config' => $context->getconfig(),
    			'type' => $type,
    			'options' => $options,
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

    public function updateAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();

    	$type = $this->params()->fromRoute('type', null);
    	$product_id = (int) $this->params()->fromRoute('product_id', 0);

    	$id = (int) $this->params()->fromRoute('id', 0);
    	if ($id) $option = ProductOption::get($id);
    	else $option = ProductOption::instanciate($type, $product_id);
    	
    	$action = $this->params()->fromRoute('act', null);
    
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
    			$data = array();
    			$data['type'] = $type;
    			$data['reference'] = $request->getPost(('option-reference'));
    			$data['caption'] = $request->getPost(('option-caption'));
    			$data['description'] = $request->getPost(('option-description'));
    			$data['is_available'] = $request->getPost(('option-is_available'));
    			$variantNumber = $request->getPost('option-variant-number');
    			$data['variants'] = array();
    			for ($i = 0; $i < $variantNumber; $i++) {
    				$variant = array('price' => $request->getPost('option-price_'.$i));
    				$data['variants'][] = $variant;
    			}
    			$data['vat_id'] = $request->getPost(('option-vat_id'));
    			if ($option->loadData($data) != 'OK') throw new \Exception('Integrity');
    			// Atomicity
    			$connection = ProductOption::getTable()->getAdapter()->getDriver()->getConnection();
    			$connection->beginTransaction();
    			try {
					if (!$option->id) $rc = $option->add();
	    			elseif ($action == 'delete') {
	    				$option->status = 'deleted';
	    				$rc = $option->update($request->getPost('update_time'));
	    			}
	    			else {
    					$rc = $option->update($request->getPost('update_time'));
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
    			'product_id' => $product_id,
    			'id' => $id,
    			'action' => $action,
    			'option' => $option,
    			'csrfForm' => $csrfForm,
    			'message' => $message,
    			'error' => $error
    	));
   		$view->setTerminal(true);
   		return $view;
    }
}
