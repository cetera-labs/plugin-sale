<?php
include('common_bo.php');
$a = \Cetera\Application::getInstance();
$t = $a->getTranslator();

$coupon_modes = [
	1 => $t->_('Одноразовый'),
	2 => $t->_('Многоразовый'),
];

if ($_GET['action'] == 'update' || $_GET['action'] == 'create') {
	$data = json_decode(file_get_contents("php://input"), true);
	$d = $a->getDbConnection()->fetchColumn('SELECT COUNT(*) FROM sale_coupon WHERE id<>? and code=?', [(int)$data['id'],$data['code']]);
	if ($d) {
		throw new \Exception( $t->_('Купон с таким кодом уже существует') );
	}
}

if (isset($_GET['action'])) {
	$data = json_decode(file_get_contents("php://input"), true);
	$data['active'] = (int)$data['active'];
	
	if ($_GET['action'] == 'update')
	{
		$a->getDbConnection()->update('sale_coupon', $data, array('id' => $data['id']));	
	}
	
	if ($_GET['action'] == 'create')
	{
		$a->getDbConnection()->insert('sale_coupon', $data);
		$data['id'] = $a->getDbConnection()->lastInsertId();			
	}	
	
	if ($_GET['action'] == 'destroy')
	{
		$a->getDbConnection()->delete('sale_coupon', array('id' => $data['id']));	
	}	
}
elseif (isset($_GET['modes'])) {
	foreach ($coupon_modes as $key => $value) {
		$data[] = [
			'id'   => $key,
			'name' => $value,
		];
	}
}
else {
	$data = $a->getDbConnection()->fetchAll('SELECT A.*, B.name as discount_name FROM sale_coupon A LEFT JOIN sale_discount B ON (A.discount_id = B.id) ORDER BY id');
	foreach ($data as $key => $value) {
		$data[$key]['mode_text'] = $coupon_modes[$data[$key]['mode']];
	}	
}

echo json_encode(array(
    'success' => true,
    'rows'    => $data
));