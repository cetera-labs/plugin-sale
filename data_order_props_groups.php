<?php
include('common_bo.php');
$a = \Cetera\Application::getInstance();

if (isset($_GET['action']))
{
	$data = json_decode(file_get_contents("php://input"), true);
	
	if ($_GET['action'] == 'update')
	{	
		$a->getDbConnection()->update('sale_order_props_group', $data, array('id' => $data['id']));	
		$data = $a->getDbConnection()->fetchAssoc('SELECT A.*, B.name as person_type FROM sale_order_props_group A LEFT JOIN sale_person_type B ON (A.person_type_id = B.id) WHERE A.id='.$data['id']);		
	}
	
	if ($_GET['action'] == 'create')
	{
		$a->getDbConnection()->insert('sale_order_props_group', $data);
		$data['id'] = $a->getDbConnection()->lastInsertId();	
		$data = $a->getDbConnection()->fetchAssoc('SELECT A.*, B.name as person_type FROM sale_order_props_group A LEFT JOIN sale_person_type B ON (A.person_type_id = B.id) WHERE A.id='.$data['id']);		
	}	
	
	if ($_GET['action'] == 'destroy')
	{
		$a->getDbConnection()->delete('sale_order_props_group', array('id' => $data['id']));
		$a->getDbConnection()->update('sale_order_props', array('group_id' => 0), array('group_id' => $data['id']));
	}	
}
else
{
	$data = $a->getDbConnection()->fetchAll('
		SELECT A.*, B.name as person_type 
		FROM sale_order_props_group A LEFT JOIN sale_person_type B ON (A.person_type_id = B.id) 
		ORDER BY B.sort, A.sort');
}

echo json_encode(array(
    'success' => true,
    'rows'    => $data
));