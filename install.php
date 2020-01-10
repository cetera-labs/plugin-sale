<?php
/*
$a = \Cetera\Application::getInstance();
$mysqli = mysqli_init();
if ($mysqli)
{
	$mysqli->options(MYSQLI_OPT_LOCAL_INFILE, true);
	$mysqli->real_connect($a->getVar('dbhost'), $a->getVar('dbuser'), $a->getVar('dbpass'), $a->getVar('dbname'));
	$mysqli->query("SET lc_messages = 'en_US'");

	$file = realpath(__DIR__).'/places.csv';
	
	$mysqli->query('
		LOAD DATA LOCAL INFILE "'.$file.'" REPLACE INTO TABLE sale_places
		CHARACTER SET utf8
		FIELDS TERMINATED BY \', \' ENCLOSED BY \'"\'
		LINES TERMINATED BY \'\n\'
		IGNORE 1 LINES
	');	
	//if ($mysqli->error) throw new \Exception($mysqli->error);
	$mysqli->close();
}
*/