<?php

if (1 || $_SERVER["REMOTE_ADDR"] != "127.0.0.1")
{
	$mysql_options = array(
		'host' => '',
		'user' => 'pohody',
		'password' => 'kukushka',
		'database' => 'pohody' );
}
else
{
	$mysql_options = array(
		'host' => '',
		'user' => 'root',
		'password' => '',
		'database' => 'pohody' );
}

?>
