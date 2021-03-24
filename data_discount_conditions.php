<?php
include('common_bo.php');
$a = \Cetera\Application::getInstance();

$rows = \Sale\Discount::getConditions();

$data = $a->getDbConnection()->fetchAll('SELECT field_id as id, describ as name, type, name as alias FROM types_fields WHERE id='.\Sale\Product::getObjectDefinition()->id.' and  name NOT IN ("tag","type","typ") and type IN ('.FIELD_TEXT.','.FIELD_INTEGER.','.FIELD_DOUBLE.','.FIELD_LINK.','.FIELD_ENUM.','.FIELD_BOOLEAN.') ORDER BY tag');
foreach ($data as $d) {
	$d['name'] = $a->decodeLocaleString($d['name']);
	$rows[] = $d;
}

$data = $a->getDbConnection()->fetchAll('SELECT field_id as id, describ as name, type, name as alias FROM types_fields WHERE id='.\Sale\Offer::getObjectDefinition()->id.' and  name NOT IN ("tag","type","typ") and type IN ('.FIELD_TEXT.','.FIELD_INTEGER.','.FIELD_DOUBLE.','.FIELD_LINK.','.FIELD_ENUM.','.FIELD_BOOLEAN.') ORDER BY tag');
foreach ($data as $d) {
	$d['name'] = '[offer] '.$a->decodeLocaleString($d['name']);
	$rows[] = $d;
}

echo json_encode([
    'success' => true,
    'rows'    => $rows
]);