<?php
	// establishing connection
	$connection = new mysqli("localhost","root","mysqlpass","CMS");
	// error checking
	if ($connection->connect_errno) {
    echo "Failed to connect to MySQL: (" . $connection->connect_errno . ") " . $connection->connect_error;
	}
	$base_url='http://cmsiitd.esy.es/api/v1/';
?>
