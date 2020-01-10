<?php
include('common_bo.php');
$a = \Cetera\Application::getInstance();

if (isset($_GET['action']))
{
	$data = json_decode(file_get_contents("php://input"), true);
	$data['active'] = (int)$data['active'];
	
	if ($_GET['action'] == 'update')
	{
		$d = $data;
		unset($d['delivery_methods']);	
		unset($d['person_types']);	
		$a->getDbConnection()->update('sale_payment', $d, array('id' => $data['id']));
		setDelivery($data['id'],$data['delivery_methods']);	
		setPerson($data['id'],$data['person_types']);		
	}
	
	if ($_GET['action'] == 'create')
	{
		$d = $data;
		unset($d['delivery_methods']);
		unset($d['person_types']);
		$a->getDbConnection()->insert('sale_payment', $d);
		$data['id'] = $a->getDbConnection()->lastInsertId();
		setDelivery($data['id'],$data['delivery_methods']);
		setPerson($data['id'],$data['person_types']);
	}	
	
	if ($_GET['action'] == 'destroy')
	{
		$a->getDbConnection()->delete('sale_payment', array('id' => $data['id']));	
		clearDelivery($data['id']);
		clearPerson($data['id']);
	}	
}
else
{
	$data = $a->getDbConnection()->fetchAll('
		SELECT A.*, GROUP_CONCAT(DISTINCT B.delivery_id SEPARATOR ",") AS delivery_methods, GROUP_CONCAT(DISTINCT C.person_type_id SEPARATOR ",") AS person_types
		FROM sale_payment A 
			LEFT JOIN sale_delivery_payment B ON (A.id=B.payment_id) 
			LEFT JOIN sale_payment_person_type C ON (A.id=C.payment_id) 
		GROUP BY id ORDER BY tag
	');
}

echo json_encode(array(
    'success' => true,
    'rows'    => $data
));

function clearDelivery($id) {
	$a = \Cetera\Application::getInstance();
	$a->getDbConnection()->delete('sale_delivery_payment', array('payment_id' => $id));		
}

function setDelivery($id,$data) {
	clearDelivery($id);
	$a = \Cetera\Application::getInstance();
	$methods = explode(',',$data);
	foreach($methods as $m)
	{
		if (!(int)$m) continue;
		$a->getDbConnection()->insert('sale_delivery_payment', array(
			'payment_id' => $id,
			'delivery_id'=> $m
		));
	}
}

function clearPerson($id) {
	$a = \Cetera\Application::getInstance();
	$a->getDbConnection()->delete('sale_payment_person_type', array('payment_id' => $id));		
}

function setPerson($id,$data) {
	clearPerson($id);
	$a = \Cetera\Application::getInstance();
	$methods = explode(',',$data);
	foreach($methods as $m)
	{
		if (!(int)$m) continue;
		$a->getDbConnection()->insert('sale_payment_person_type', array(
			'payment_id' => $id,
			'person_type_id'=> $m
		));
	}
}