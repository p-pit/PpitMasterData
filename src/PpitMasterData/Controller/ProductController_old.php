<?php

namespace PpitMasterData\Controller;

use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use PpitMasterData\Model\Product;
use PpitMasterData\Model\ProductCategory;
use PpitMasterData\Model\ProductOptionMatrix;
use PpitMasterData\Form\ProductForm;
use PpitCore\Model\Csrf;
use PpitCore\Model\Context;
use PpitCore\Form\CsrfForm;
use SplFileObject;
use Zend\Db\Sql\Expression;
use Zend\Mvc\Controller\AbstractActionController;

class ProductController extends AbstractActionController
{
    public function indexAction()
    {    	
        $product_category_id = (int) $this->params()->fromRoute('product_category_id', 0);
        $productCategory = ProductCategory::get($product_category_id);

    	// Retrieve the context
    	$context = Context::getCurrent();

    	// Prepare the SQL request
    	$major = $this->params()->fromQuery('major', NULL);
    	if (!$major) $major = 'caption';
    	$dir = $this->params()->fromQuery('dir', NULL);
    	if (!$dir) $dir = 'ASC';

    	$select = Product::getTable()->getSelect()
    		->join('md_product_category', 'md_product.product_category_id = md_product_category.id', array('product_category' => 'caption'), 'left')
	    	->order(array($major.' '.$dir, 'caption'));

    	$brand = $this->params()->fromQuery('brand', NULL);
    	if ($brand) $select->where(array('brand' => $brand));

    	// If a product category has been given, filter on it
    	if ($product_category_id) {
    		$select->where(array('md_product.product_category_id' => $product_category_id));
    	}

    	$cursor = Product::getTable()->selectWith($select);
    	$products = array();
    	foreach ($cursor as $product) $products[] = $product;

    	$view = new ViewModel(array(
    			'context' => $context,
				'config' => $context->getconfig(),
    			'major' => $major,
    			'dir' => $dir,
    			'brand' => $brand,
    			'productCategory' => $productCategory,
    			'products' => $products,
    			'product_category_id' => $product_category_id,
    	));
   		if ($context->isSpaMode()) $view->setTerminal(true);
   		return $view;
    }

    public function dataListAction()
    {
    	$product_category_id = (int) $this->params()->fromRoute('product_category_id', 0);
    	$productCategory = ProductCategory::get($product_category_id);
    
    	// Retrieve the context
    	$context = Context::getCurrent();
    
    	// Prepare the SQL request
    	$major = $this->params()->fromQuery('major', NULL);
    	if (!$major) $major = 'caption';
    	$dir = $this->params()->fromQuery('dir', NULL);
    	if (!$dir) $dir = 'ASC';
    
    	$select = Product::getTable()->getSelect()
    	->join('md_product_category', 'md_product.product_category_id = md_product_category.id', array('product_category' => 'caption'), 'left')
    	->order(array($major.' '.$dir, 'caption'));
    
    	$brand = $this->params()->fromQuery('brand', NULL);
    	if ($brand) $select->where(array('brand' => $brand));
    
    	// If a product category has been given, filter on it
    	if ($product_category_id) {
    		$select->where(array('md_product.product_category_id' => $product_category_id));
    	}

    	$cursor = Product::getTable()->selectWith($select);
    	$data = array();
    	foreach ($cursor as $product) {
    		$data[] = array(
    				'id' => $product->id,
    				'caption' => $product->caption,
    		);
    	}
    	return new JsonModel(array('data' => $data));
    }
    
    public function update($returnRoute)
    {
    	// Retrieve the context
    	$context = Context::getCurrent();

    	$product_category_id = (int) $this->params()->fromRoute('product_category_id', 0);
    	$id = (int) $this->params()->fromRoute('id', 0);
    	if ($id) $product = Product::get($id);
    	else $product = Product::instanciate($product_category_id);
    	 
    	$csrfForm = new CsrfForm();
    	$csrfForm->addCsrfElement('csrf');
    	$message = null;
    	$error = null;
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    		$csrfForm->setInputFilter((new Csrf('csrf'))->getInputFilter());
    		$csrfForm->setData($request->getPost());
    
    		if ($csrfForm->isValid()) { // CSRF check
    			$return = $product->loadDataFromRequest($request);

				if ($return != 'OK') $error = $return;
    			else {
    			
	    			// Atomicity
	    			$connection = Product::getTable()->getAdapter()->getDriver()->getConnection();
	    			$connection->beginTransaction();
	    			try {
			    		// Add or update
	    				if (!$id) $return = $product->add();
	    				else $return = $product->update($request->getPost('update_time'));
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
    			'product' => $product,
    			'product_category_id' => $product_category_id,
       			'id' => $id,
    			'csrfForm' => $csrfForm,
    			'message' => $message,
    			'error' => $error
    	));
   		if ($context->isSpaMode()) $view->setTerminal(true);
   		return $view;
    }
    
    public function updateAction() {
    	return $this->update('product/index');
    }
/*
    public function matrixAction()
    {
        $product_category_id = (int) $this->params()->fromRoute('product_category_id', 0);
    	$id = (int) $this->params()->fromRoute('id', 0);
    	if (!$id) {
    		return $this->redirect()->toRoute('index');
    	}
    	// Retrieve the current user
    	$currentUser = Functions::getUser($this);
    	$currentUser->retrieveHabilitations($this);
    
    	// Retrieve the product
    	$product = $this->getProductTable()->get($id, $currentUser);
    	 
    	//Retrieve the options
    	$select = $this->getProductOptionTable()->getSelect()
	    	->where(array('product_id' => $product->id))
    		->order(array('id'));
    	$cursor = $this->getProductOptionTable()->selectWith($select, $currentUser);
    	$options = array();
    	foreach ($cursor as $option) $options[] = $option;
    
    	// Generate the constraint matrix
    	$select = $this->getProductOptionTable()->getSelect()
	    	->where(array('product_id' => $id))
    		->order(array('id'));
    	$cursor = $this->getProductOptionTable()->selectWith($select, $currentUser);
    	$options = array();
    	foreach ($cursor as $option) $options[] = $option;
    	$matrix = array();
    	foreach ($options as $row) {
    		$matrix[$row->id] = array();
    		foreach ($options as $cell) $matrix[$row->id][$cell->id] = 0;
    	}
    	// Retrieve the previous constraints
    	$select = $this->getProductOptionMatrixTable()->getSelect()
	    	->where(array('product_id' => $id));
    	$cursor = $this->getProductOptionMatrixTable()->selectWith($select, $currentUser);
    	foreach ($cursor as $cell) $matrix[$cell->row_option_id][$cell->col_option_id] = $cell->constraint;

    	$csrfForm = new CsrfForm();
    	$csrfForm->addCsrfElement('csrf');
    	$error = null;
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    		$csrfForm->setInputFilter((new Csrf('csrf'))->getInputFilter());
    		$csrfForm->setData($request->getPost());
    
    		if ($csrfForm->isValid()) { // CSRF check
	    		$productOptionMatrix = new ProductOptionMatrix();

    			// Delete the previous constraints
    			$this->getProductOptionMatrixTable()->multipleDelete(array('product_id' => $id), $currentUser);
    
    			$productOptionMatrix->id = NULL;
    			$productOptionMatrix->product_id = $id;
    			// Retrieve and save the new dependence constraints from the form
    			foreach ($matrix as $row_id => $row) {
    				foreach ($row as $col_id => $constraint) {
    					if ($row_id != $col_id && $request->getPost('dep_'.$row_id.'_'.$col_id) == 1) {
    						$productOptionMatrix->row_option_id = $row_id;
    						$productOptionMatrix->col_option_id = $col_id;
    						$productOptionMatrix->constraint = 1; // 1 for dependence
    						$this->getProductOptionMatrixTable()->save($productOptionMatrix, $currentUser);
    					}
    				}
    			}
    			// Retrieve and save the new exclusion constraints from the form
    			foreach ($matrix as $row_id => $row) {
    				foreach ($row as $col_id => $constraint) {
    					if ($row_id != $col_id && $request->getPost('exc_'.$row_id.'_'.$col_id) == 1) {
    						$productOptionMatrix->row_option_id = $row_id;
    						$productOptionMatrix->col_option_id = $col_id;
    						$productOptionMatrix->constraint = 2; // 2 for exclusion
    						$this->getProductOptionMatrixTable()->save($productOptionMatrix, $currentUser);
    					}
    				}
    			}
    			// Redirect to index
    			return $this->redirect()->toRoute('product', array('product_category_id' => $product_category_id));
    		}
    	}
    	return array(
    			'currentUser' => $currentUser,
    			'csrfForm' => $csrfForm,
    			'product_category_id' => $product_category_id,
       			'id' => $id,
    			'matrix' => $matrix,
    			'productOptionTable' => $this->getProductOptionTable(),
    			'product' => $product
    	);
    }*/
    
	public function deleteAction()
    {
    	$id = (int) $this->params()->fromRoute('id', 0);
    	if (!$id) return $this->redirect()->toRoute('index');

    	// Retrieve the current user
    	$context = Context::getCurrent();

    	// Retrieve the product
		$product = Product::get($id);
    	
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
    			$connection = Product::getTable()->getAdapter()->getDriver()->getConnection();
    			$connection->beginTransaction();
    			try {
		    		// Delete the row
					$return = $product->delete($request->getPost('update_time'));
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
    		'product' => $product,
    		'id' => $id,
    		'csrfForm' => $csrfForm,
    		'message' => $message,
    		'error' => $error,
    	));
   		if ($context->isSpaMode()) $view->setTerminal(true);
   		return $view;
    }
}
    