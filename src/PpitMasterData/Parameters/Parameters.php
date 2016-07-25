<?php
namespace PpitMasterData\Parameters;

class Parameters {

	public static $categories = array('Catalogue' => 'Catalogue');
	
	public static $defaultItemCount = 10;

	public static $types = array('Service' => 'Service', 'Department' => 'Department', 'Direction' => 'Direction', 'Division' => 'Division', 'Company' => 'Company', 'Group' => 'Group', 'Place of business' => 'Place of business');
	
	public static $years = array('2015' => '2015', '2016' => '2016', '2017' => '2017', '2018' => '2018', '2019' => '2019', '2020' => '2020');

	public static $vatRates = array('Exonéré' => 0, '5,5 %' => 0.055, '20 %' => 0.2);
	
	public static $taxonomy = array(
		'critere_1' => array('Valeur 1' => 'Valeur 1', 'Valeur 2' => 'Valeur 2', 'Valeur 3' => 'Valeur 3'),
		'critere_2' => array('Valeur 1' => 'Valeur 1', 'Valeur 2' => 'Valeur 2', 'Valeur 3' => 'Valeur 3')
	);
}