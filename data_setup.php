<?php
include('common_bo.php');

if (count($_POST)) 
	foreach ($_POST as $name => $value)
		\Sale\Setup::configSet( $name, $value );

echo json_encode(\Sale\Setup::configGetAll());