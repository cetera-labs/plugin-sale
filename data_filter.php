<?php
include('common_bo.php');
$a = \Cetera\Application::getInstance();

$filter = \Sale\Filter::get( 'filter', \Sale\Product::getObjectDefinition() );

if (isset($_GET['action']))
{
	$data = json_decode(file_get_contents("php://input"), true);
	
	if ($_GET['action'] == 'destroy')
	{
		$filter->deleteField($data['id']);	
	}	

	if ($_GET['action'] == 'create')
	{
		$filter->addField($data);	
	}	

	if ($_GET['action'] == 'update')
	{
		$filter->updateField($data);	
	}	
}
else
{
	$data   = $filter->getFields();
}

echo json_encode(array(
    'success' => true,
    'rows'    => $data
));