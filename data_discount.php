<?php
include('common_bo.php');
$a = \Cetera\Application::getInstance();

if (isset($_GET['action']))
{
	$data = json_decode(file_get_contents("php://input"), true);
	$data['active'] = (int)$data['active'];
	$data['last_discount'] = (int)$data['last_discount'];
	
	if ($_GET['action'] == 'update')
	{
		$a->getDbConnection()->update('sale_discount', $data, array('id' => $data['id']));	
	}
	
	if ($_GET['action'] == 'create')
	{
		$a->getDbConnection()->insert('sale_discount', $data);
		$data['id'] = $a->getDbConnection()->lastInsertId();	
		$data['value_text'] = value_text($data);		
	}	
	
	if ($_GET['action'] == 'destroy')
	{
		$a->getDbConnection()->delete('sale_discount', array('id' => $data['id']));	
	}	
}
else
{
	$data = $a->getDbConnection()->fetchAll('SELECT * FROM sale_discount ORDER BY priority');
	foreach ($data as $key => $value)
	{
		$data[$key]['value_text'] = value_text($data[$key]);
	}
}

echo json_encode(array(
    'success' => true,
    'rows'    => $data
));

function value_text($data)
{
		switch ($data['value_type'])
		{
			case 0:
				return $data['value'].'%';
			case 1:
				return $data['value'];
			case 2:
				return '= '.$data['value'];
		}	
}