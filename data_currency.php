<?php
include('common_bo.php');
$a = \Cetera\Application::getInstance();
$t = $a->getTranslator();

if (isset($_GET['action']))
{
	$data = json_decode(file_get_contents("php://input"), true);
	$data['prime'] = (int)$data['prime'];
	
	if ($_GET['action'] != 'destroy') {
		if ($data['prime']) {
			$data['rate'] = 1;
			$data['rate_cnt'] = 1;
			$a->getDbConnection()->update('sale_currency', ['`prime`' => 0], ['`prime`' => 1]);	
		}
		else {
			$prime = $a->getDbConnection()->fetchColumn('SELECT COUNT(*) FROM sale_currency WHERE prime=1 and id<>'.(int)$data['id']);
			if (!$prime) {
				$data['prime'] = 1;
				$data['rate'] = 1;
				$data['rate_cnt'] = 1;				
			}
		}
	}
	
	if ($_GET['action'] == 'update')
	{	
		$a->getDbConnection()->update('sale_currency', $data, array('id' => $data['id']));	
	}
	
	if ($_GET['action'] == 'create')
	{
		$a->getDbConnection()->insert('sale_currency', $data);
		$data['id'] = $a->getDbConnection()->lastInsertId();		
	}	
	
	if ($_GET['action'] == 'destroy')
	{
		$deleted = $a->getDbConnection()->delete('sale_currency', array(
			'id' => $data['id'],
			'`prime`' => 0
		));
		if (!$deleted) {
			throw new \Exception($t->_('Нельзя удалить базовую валюту'));
		}
	}	
}
else
{
	$data = $a->getDbConnection()->fetchAll('SELECT * FROM sale_currency ORDER BY sort');
}

echo json_encode(array(
    'success' => true,
    'rows'    => $data
));