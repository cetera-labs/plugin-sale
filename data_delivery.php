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
		unset($d['payment_methods']);		
		$a->getDbConnection()->update('sale_delivery', $d, array('id' => $data['id']));	
		setPayment($data['id'],$data['payment_methods']);		
	}
	
	if ($_GET['action'] == 'create')
	{
		$d = $data;
		unset($d['payment_methods']);			
		$a->getDbConnection()->insert('sale_delivery', $d);
		$data['id'] = $a->getDbConnection()->lastInsertId();
		setPayment($data['id'],$data['payment_methods']);
	}	
	
	if ($_GET['action'] == 'destroy')
	{
		$a->getDbConnection()->delete('sale_delivery', array('id' => $data['id']));	
		clearPayment( $data['id']);
	}	
}
else
{
	$data = $a->getDbConnection()->fetchAll('SELECT A.*, GROUP_CONCAT(B.payment_id SEPARATOR ",") AS payment_methods FROM sale_delivery A LEFT JOIN sale_delivery_payment B ON (A.id=B.delivery_id) GROUP BY id ORDER BY tag');
}

echo json_encode(array(
    'success' => true,
    'rows'    => $data
));

function clearPayment($id) {
	$a = \Cetera\Application::getInstance();
	$a->getDbConnection()->delete('sale_delivery_payment', array('delivery_id' => $id));		
}

function setPayment($id,$data) {
	clearPayment($id);
	$a = \Cetera\Application::getInstance();
	$methods = explode(',',$data);
	foreach($methods as $m)
	{
		if (!(int)$m) continue;
		$a->getDbConnection()->insert('sale_delivery_payment', array(
			'delivery_id' => $id,
			'payment_id'=> $m
		));
	}
}