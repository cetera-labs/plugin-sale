<?php
include('common_bo.php');
$a = \Cetera\Application::getInstance();
$t = $a->getTranslator();

if (isset($_GET['action']))
{
	$data = json_decode(file_get_contents("php://input"), true);
	$data['active'] = (int)$data['active'];
	
	if ($_GET['action'] == 'update')
	{	
		$a->getDbConnection()->update('sale_person_type', $data, array('id' => $data['id']));	
	}
	
	if ($_GET['action'] == 'create')
	{
		$a->getDbConnection()->insert('sale_person_type', $data);
		$data['id'] = $a->getDbConnection()->lastInsertId();		
	}	
	
	if ($_GET['action'] == 'destroy')
	{
		if ($data['id'] != 1)
		{
			$a->getDbConnection()->delete('sale_person_type', array('id' => $data['id']));
			$a->getDbConnection()->delete('sale_payment_person_type', array('person_type_id' => $data['id']));
		}
		else
		{
			throw new \Exception($t->_('Нельзя удалить плательщика с ID=1'));
		}
	}	
}
else
{
	$data = $a->getDbConnection()->fetchAll('SELECT * FROM sale_person_type ORDER BY sort');
}

echo json_encode(array(
    'success' => true,
    'rows'    => $data
));