<?php
include('common_bo.php');

if (!isset($_GET['action'])) {
    $_GET['action'] = false;
}

if ($_GET['action'] == 'get_status_list') {
	echo json_encode(array(
		'rows'    => \Sale\Order::callStatic('getStatuses')
	));	
	die();
}
elseif ($_GET['action'] == 'get_pay_status_list') {
	echo json_encode(array(
		'rows'    => \Sale\Order::callStatic('getPayStatuses')
	));	
	die();
}
elseif ($_GET['action'] == 'update') {
	$d = json_decode(file_get_contents("php://input"), true);
	
	$order = \Sale\Order::getById( $d['id'] );
	$order->setParams( $d );	
	$order->save();
	$orders = \Sale\Order::enum()->where('sale_orders.id='.$d['id']);
}
elseif ($_GET['action'] == 'delete') {
	$d = json_decode(file_get_contents("php://input"), true);
	
	$order = \Sale\Order::getById( $d['id'] );
	$order->delete();
	die();
}
else {
	$orders = \Sale\Order::enum()->setItemCountPerPage( $_GET['limit'] )->setCurrentPageNumber( $_GET['page'] );
	if (isset($_GET['query'])) $orders->search( $_GET['query'] );
	$orders->filter($_GET);
}

if (isset($_GET['sort'])) {
	$orders->orderBy($_GET['sort'],$_GET['dir']);
}
$data = array();

foreach ($orders as $o)
	$data[] = $o->asArray();

echo json_encode(array(
    'success' => true,
	'total'   => $orders->getCountAll(),
    'rows'    => $data
));