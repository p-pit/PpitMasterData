<?php
namespace PpitMasterData;

use PpitCore\Model\GenericTable;
use PpitMasterData\Model\Product;
use PpitMasterData\Model\ProductCategory;
use PpitMasterData\Model\ProductOption;
use PpitMasterData\Model\ProductOptionMatrix;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Authentication\Storage;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;

class Module //implements AutoloaderProviderInterface, ConfigProviderInterface
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'PpitMasterData\Model\ProductTable' => function($sm) {
                    $tableGateway = $sm->get('ProductTableGateway');
                    $table = new GenericTable($tableGateway);
                    return $table;
                },
                'ProductTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Product());
                    return new TableGateway('md_product', $dbAdapter, null, $resultSetPrototype);
                },
	          	'PpitMasterData\Model\ProductCategoryTable' => function($sm) {
                    $tableGateway = $sm->get('ProductCategoryTableGateway');
                    $table = new GenericTable($tableGateway);
                    return $table;
                },
                'ProductCategoryTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new ProductCategory());
                    return new TableGateway('md_product_category', $dbAdapter, null, $resultSetPrototype);
                },
                'PpitMasterData\Model\ProductOptionTable' =>  function($sm) {
                    $tableGateway = $sm->get('ProductOptionTableGateway');
                    $table = new GenericTable($tableGateway);
                    return $table;
                },
                'ProductOptionTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new ProductOption());
                    return new TableGateway('md_product_option', $dbAdapter, null, $resultSetPrototype);
                },
                'PpitMasterData\Model\ProductOptionMatrixTable' =>  function($sm) {
                    $tableGateway = $sm->get('ProductOptionMatrixTableGateway');
                    $table = new GenericTable($tableGateway);
                    return $table;
                },
                'ProductOptionMatrixTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new ProductOptionMatrix());
                    return new TableGateway('md_product_option_matrix', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
