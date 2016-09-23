<?php

return array(
	'controllers' => array(
		'invokables' => array(

			'PpitMasterData\Controller\OrgUnit' => 'PpitMasterData\Controller\OrgUnitController',
			'PpitMasterData\Controller\OrgUnitContact' => 'PpitMasterData\Controller\OrgUnitContactController',
			'PpitMasterData\Controller\Place' => 'PpitMasterData\Controller\PlaceController',
			'PpitMasterData\Controller\Product' => 'PpitMasterData\Controller\ProductController',
			'PpitMasterData\Controller\ProductCategory' => 'PpitMasterData\Controller\ProductCategoryController',
			'PpitMasterData\Controller\ProductOption' => 'PpitMasterData\Controller\ProductOptionController',
        	
		),
	),
 
	'router' => array(
		'routes' => array(
			'index' => array(
				'type' => 'literal',
				'options' => array(
					'route'    => '/',
					'defaults' => array(
						'controller' => 'CitMasterData\Controller\Product',
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'index' => array(
						'type' => 'segment',
						'options' => array(
							'route' => '/index',
							'defaults' => array(
								'action' => 'index',
							),
						),
					),
				),
			),
			'orgUnit' => array(
				'type'    => 'literal',
				'options' => array(
					'route'    => '/org-unit',
					'defaults' => array(
						'controller' => 'PpitMasterData\Controller\OrgUnit',
						'action'     => 'index',
					),
				),
				'may_terminate' => true,
					'child_routes' => array(
						'index' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/index',
								'defaults' => array(
									'action' => 'index',
								),
							),
						),
						'list' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/list',
								'defaults' => array(
									'action' => 'list',
								),
							),
						),
						'dataList' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/data-list',
								'defaults' => array(
									'action' => 'dataList',
								),
							),
						),
						'update' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/update[/:parent_id][/:id]',
								'constraints' => array(
									'parent_id' => '[0-9]*',
									'id' => '[0-9]*',
								),
								'defaults' => array(
									'action' => 'update',
								),
							),
						),
						'delete' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/delete[/:id]',
								'constraints' => array(
									'id'     => '[0-9]*',
								),
								'defaults' => array(
									'action' => 'delete',
								),
							),
						),
					),
				),
			'orgUnitContact' => array(
				'type'    => 'literal',
				'options' => array(
					'route'    => '/org-unit-contact',
					'defaults' => array(
						'controller' => 'PpitMasterData\Controller\OrgUnitContact',
						'action'     => 'list',
					),
				),
				'may_terminate' => true,
					'child_routes' => array(
						'list' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/list[/:org_unit_id]',
								'constraints' => array(
									'org_unit_id' => '[0-9]*',
								),
								'defaults' => array(
									'action' => 'list',
								),
							),
						),
						'add' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/add[/:org_unit_id]',
								'constraints' => array(
									'org_unit_id' => '[0-9]*',
								),
								'defaults' => array(
									'action' => 'add',
								),
							),
						),
						'delete' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/delete[/:id]',
								'constraints' => array(
									'id'     => '[0-9]*',
								),
								'defaults' => array(
									'action' => 'delete',
								),
							),
						),
					),
				),
			'place' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/place',
                    'defaults' => array(
                        'controller' => 'PpitMasterData\Controller\Place',
                        'action'     => 'index',
                    ),
                ),
           		'may_terminate' => true,
	       		'child_routes' => array(
	                'index' => array(
	                    'type' => 'segment',
	                    'options' => array(
	                        'route' => '/index',
	                    	'defaults' => array(
	                    		'action' => 'index',
	                        ),
	                    ),
	                ),
	                'list' => array(
	                    'type' => 'segment',
	                    'options' => array(
	                        'route' => '/list',
	                    	'defaults' => array(
	                    		'action' => 'list',
	                        ),
	                    ),
	                ),
	                'dataList' => array(
	                    'type' => 'segment',
	                    'options' => array(
	                        'route' => '/data-list',
	                    	'defaults' => array(
	                    		'action' => 'dataList',
	                        ),
	                    ),
	                ),
	       			'update' => array(
	                    'type' => 'segment',
	                    'options' => array(
	                        'route' => '/update[/:id]',
		                    'constraints' => array(
		                    	'id'     => '[0-9]*',
		                    ),
	                    	'defaults' => array(
	                            'action' => 'update',
	                        ),
	                    ),
	                ),
	       			'delete' => array(
	                    'type' => 'segment',
	                    'options' => array(
	                        'route' => '/delete[/:id]',
		                    'constraints' => array(
		                    	'id'     => '[0-9]*',
		                    ),
	                    	'defaults' => array(
	                            'action' => 'delete',
	                        ),
	                    ),
	                ),
	       		),
	       	),
        	'product' => array(
        				'type'    => 'literal',
        				'options' => array(
        						'route'    => '/product',
        						'defaults' => array(
        								'controller' => 'PpitMasterData\Controller\Product',
        								'action'     => 'index',
        						),
        				),
        				'may_terminate' => true,
        				'child_routes' => array(
        						'index' => array(
        								'type' => 'segment',
        								'options' => array(
        										'route' => '/index[/:type]',
        										'defaults' => array(
        												'action' => 'index',
        										),
        								),
        						),
        						'search' => array(
        								'type' => 'segment',
        								'options' => array(
        										'route' => '/search[/:type]',
        										'defaults' => array(
        												'action' => 'search',
        										),
        								),
        						),
        						'list' => array(
        								'type' => 'segment',
        								'options' => array(
        										'route' => '/list[/:type]',
        										'defaults' => array(
        												'action' => 'list',
        										),
        								),
        						),
        						'detail' => array(
        								'type' => 'segment',
        								'options' => array(
        										'route' => '/detail[/:type][/:id]',
        										'constraints' => array(
        												'id' => '[0-9]*',
        										),
        										'defaults' => array(
        												'action' => 'detail',
        										),
        								),
        						),
        						'dataList' => array(
        								'type' => 'segment',
        								'options' => array(
        										'route' => '/data-list[/:product_category_id]',
        										'constraints' => array(
        												'product_category_id' => '[0-9]*',
        										),
        										'defaults' => array(
        												'action' => 'dataList',
        										),
        								),
        						),
        						'update' => array(
        								'type' => 'segment',
        								'options' => array(
        										'route' => '/update[/:product_category_id][/:id]',
        										'constraints' => array(
        												'product_category_id' => '[0-9]*',
        												'id'     => '[0-9]*',
        										),
        										'defaults' => array(
        												'action' => 'update',
        										),
        								),
        						),
        						'matrix' => array(
        								'type' => 'segment',
        								'options' => array(
        										'route' => '/matrix[/:product_category_id][/:id]',
        										'constraints' => array(
        												'product_category_id' => '[0-9]*',
        												'id'     => '[0-9]*',
        										),
        										'defaults' => array(
        												'action' => 'matrix',
        										),
        								),
        						),
        						'export' => array(
        								'type' => 'segment',
        								'options' => array(
        										'route' => '/export',
        										'defaults' => array(
        												'action' => 'export',
        										),
        								),
        						),
        						'delete' => array(
        								'type' => 'segment',
        								'options' => array(
        										'route' => '/delete[/:id]',
        										'constraints' => array(
        												'id'     => '[0-9]*',
        										),
        										'defaults' => array(
        												'action' => 'delete',
        										),
        								),
        						),
				),),

        		'productCategory' => array(
        				'type'    => 'literal',
        				'options' => array(
        						'route'    => '/product-category',
        						'defaults' => array(
        								'controller' => 'PpitMasterData\Controller\ProductCategory',
        								'action'     => 'index',
        						),
        				),
        				'may_terminate' => true,
        				'child_routes' => array(
        						'index' => array(
        								'type' => 'segment',
        								'options' => array(
        										'route' => '/index',
        										'defaults' => array(
        												'action' => 'index',
        										),
        								),
        						),
        						'update' => array(
        								'type' => 'segment',
        								'options' => array(
        										'route' => '/update[/:id]',
        										'constraints' => array(
        												'id'     => '[0-9]*',
        										),
        										'defaults' => array(
        												'action' => 'update',
        										),
        								),
        						),
        						'delete' => array(
        								'type' => 'segment',
        								'options' => array(
        										'route' => '/delete[/:id]',
        										'constraints' => array(
        												'id'     => '[0-9]*',
        										),
        										'defaults' => array(
        												'action' => 'delete',
        										),
        								),
        						),
        				),),
        		
        		'productOption' => array(
        				'type'    => 'literal',
        				'options' => array(
        						'route'    => '/product-option',
        						'defaults' => array(
        								'controller' => 'PpitMasterData\Controller\ProductOption',
        								'action'     => 'index',
        						),
        				),
        				'may_terminate' => true,
        				'child_routes' => array(
        						'index' => array(
        								'type' => 'segment',
        								'options' => array(
        										'route' => '/index[/:product_id]',
        										'constraints' => array(
        												'product_id' => '[0-9]*',
        										),
        										'defaults' => array(
        												'action' => 'index',
        										),
        								),
        						),
/*        						'add' => array(
        								'type' => 'segment',
        								'options' => array(
        										'route' => '/add[/:id]',
        										'constraints' => array(
        												'id'     => '[0-9]*',
        										),
        										'defaults' => array(
        												'action' => 'add',
        										),
        								),
        						),*/
        						'update' => array(
        								'type' => 'segment',
        								'options' => array(
        										'route' => '/update[/:product_id][/:id]',
        										'constraints' => array(
        												'product_id' => '[0-9]*',
        												'id' => '[0-9]*',
        										),
        										'defaults' => array(
        												'action' => 'update',
        										),
        								),
        						),
        						'delete' => array(
        								'type' => 'segment',
        								'options' => array(
        										'route' => '/delete[/:id]',
        										'constraints' => array(
        												'id'     => '[0-9]*',
        										),
        										'defaults' => array(
        												'action' => 'delete',
        										),
        								),
        						),
        				),),
        		
        		
        		
        		
        		
        		
        ),
    ),
	
	'bjyauthorize' => array(
		// Guard listeners to be attached to the application event manager
		'guards' => array(
			'BjyAuthorize\Guard\Route' => array(

				// Organizational unit
				array('route' => 'orgUnit', 'roles' => array('admin')),
				array('route' => 'orgUnit/index', 'roles' => array('admin')),
				array('route' => 'orgUnit/list', 'roles' => array('admin')),
				array('route' => 'orgUnit/dataList', 'roles' => array('admin')),
				array('route' => 'orgUnit/update', 'roles' => array('admin')),
				array('route' => 'orgUnit/delete', 'roles' => array('admin')),

				// Relation organizational-unit contact
				array('route' => 'orgUnitContact/list', 'roles' => array('admin')),
				array('route' => 'orgUnitContact/add', 'roles' => array('admin')),
				array('route' => 'orgUnitContact/delete', 'roles' => array('admin')),
						
				// Place of business
				array('route' => 'place', 'roles' => array('admin')),
				array('route' => 'place/index', 'roles' => array('admin')),
				array('route' => 'place/list', 'roles' => array('admin')),
				array('route' => 'place/dataList', 'roles' => array('admin')),
				array('route' => 'place/update', 'roles' => array('admin')),
				array('route' => 'place/delete', 'roles' => array('admin')),

				// Product
				array('route' => 'product', 'roles' => array('user')),
				array('route' => 'product/index', 'roles' => array('user')),
				array('route' => 'product/search', 'roles' => array('user')),
				array('route' => 'product/detail', 'roles' => array('user')),
				array('route' => 'product/update', 'roles' => array('admin')),
				array('route' => 'product/matrix', 'roles' => array('admin')),
				array('route' => 'product/delete', 'roles' => array('admin')),
				
				// Product category
				array('route' => 'productCategory', 'roles' => array('admin')),
				array('route' => 'productCategory/index', 'roles' => array('admin')),
				array('route' => 'productCategory/update', 'roles' => array('admin')),
				array('route' => 'productCategory/delete', 'roles' => array('admin')),
				
				// Product option
				array('route' => 'productOption', 'roles' => array('admin')),
				array('route' => 'productOption/index', 'roles' => array('admin')),
				array('route' => 'productOption/update', 'roles' => array('admin')),
				array('route' => 'productOption/delete', 'roles' => array('admin')),
					
					
			)
		)
	),
/*
	'ppitMasterDataSettings' => array(
			'adminRegions' => array(
					'1' => 'Métropole',
					'2' => 'Martinique',
					'3' => 'Mayotte',
					'4' => 'Guyane',
					'5' => 'Réunion',
			),
			'vatRates' => array(
					'1' => '0.2',
					'2' => '0.085',
					'3' => '0',
					'4' => '0',
					'5' => '0.085',
			)
	),*/
		
    'view_manager' => array(
    	'strategies' => array(
    			'ViewJsonStrategy',
    	),
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',       // On défini notre doctype
        'not_found_template'       => 'error/404',   // On indique la page 404
        'exception_template'       => 'error/index', // On indique la page en cas d'exception
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
        ),
        'template_path_stack' => array(
            'ppit-master-data' => __DIR__ . '/../view',
        ),
    ),
	'translator' => array(
		'locale' => 'fr_FR',
		'translation_file_patterns' => array(
			array(
				'type'     => 'phparray',
				'base_dir' => __DIR__ . '/../language',
				'pattern'  => '%s.php',
				'text_domain' => 'ppit-master-data'
			),
	       	array(
	            'type' => 'phpArray',
	            'base_dir' => './vendor/zendframework/zendframework/resources/languages/',
	            'pattern'  => 'fr/Zend_Validate.php',
	        ),
 		),
	),
	'ppitRoles' => array(
			'PpitMasterData' => array(
			),
	),
	'ppitMasterDataDependencies' => array(
	),
	'ppitContactDependencies' => array(
			'md_place' => new \PpitMasterData\Model\Place,
	),
	'product/index' => array(
			'title' => array('en_US' => 'P-PIT Sales', 'fr_FR' => 'P-PIT Ventes'),
	),
);
