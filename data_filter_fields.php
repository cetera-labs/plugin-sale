<?php
include('common_bo.php');
$a = \Cetera\Application::getInstance();
$t = $a->getTranslator();

$rows = array();

$data = $a->getDbConnection()->fetchAll('
	SELECT id as type_id, field_id as id, describ as name, type 
	FROM types_fields 
	WHERE id='.\Sale\Product::getObjectDefinition()->id.' 
		and name NOT IN ("alias","idcat","tag","type","typ") 
		and type IN ('.FIELD_TEXT.','.FIELD_INTEGER.','.FIELD_DOUBLE.','.FIELD_LINK.','.FIELD_LINKSET.','.FIELD_ENUM.','.FIELD_BOOLEAN.','.FIELD_MATSET.') 
	ORDER BY tag');
foreach ($data as $d) {
	$d['name'] = \Sale\Product::getObjectDefinition()->getDescriptionDisplay().' - '.$a->decodeLocaleString($d['name']);
	$rows[] = $d;
}

$data = $a->getDbConnection()->fetchAll('
	SELECT id as type_id, field_id as id, describ as name, type 
	FROM types_fields 
	WHERE id='.\Sale\Offer::getObjectDefinition()->id.' 
		and name NOT IN ("code","quantity","price","autor","alias","idcat","name","tag","type","typ") 
		and type IN ('.FIELD_TEXT.','.FIELD_INTEGER.','.FIELD_DOUBLE.','.FIELD_LINK.','.FIELD_LINKSET.','.FIELD_ENUM.','.FIELD_BOOLEAN.','.FIELD_MATSET.') 
	ORDER BY tag');
foreach ($data as $d) {
	$d['name'] = \Sale\Offer::getObjectDefinition()->getDescriptionDisplay().' - '.$a->decodeLocaleString($d['name']);
	$rows[] = $d;
}

echo json_encode(array(
    'success' => true,
    'rows'    => $rows
));