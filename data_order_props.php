<?php
include('common_bo.php');
$a = \Cetera\Application::getInstance();

if (isset($_GET['action']))
{
	$data = json_decode(file_get_contents("php://input"), true);
	$data['active'] = (int)$data['active'];
	$data['is_login'] = (int)$data['is_login'];
	$data['is_email'] = (int)$data['is_email'];
	$data['is_phone'] = (int)$data['is_phone'];
	$data['required'] = (int)$data['required'];
	
	if ($_GET['action'] == 'update')
	{	
		$a->getDbConnection()->update('sale_order_props', $data, array('id' => $data['id']));	
	}
	
	if ($_GET['action'] == 'create')
	{
		$a->getDbConnection()->insert('sale_order_props', $data);
		$data['id'] = $a->getDbConnection()->lastInsertId();	
	}	
	
	if ($_GET['action'] == 'destroy')
	{
		$a->getDbConnection()->delete('sale_order_props', array('id' => $data['id']));
	}	
}

$query = '
	SELECT A.*, B.name as person_type , C.name as group_name
	FROM sale_order_props A 
		LEFT JOIN sale_person_type B ON (A.person_type_id = B.id) 
		LEFT JOIN sale_order_props_group C ON (A.group_id = C.id)';
	
if (isset($data['id'])) $query .= ' WHERE A.id='.(int)$data['id'];
$query .= ' ORDER BY B.sort,C.sort, A.sort';

$data = $a->getDbConnection()->fetchAll($query);

echo json_encode(array(
    'success' => true,
    'rows'    => $data
));