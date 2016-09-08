<?php
$project_name="My Inventory";

$DB_HOST="localhost";
$DB_PORT="5432";
$DB_NAME="inventory";
$DB_USER="inventory";
$DB_PASSWORD="myPassword";

$maintenance_states=array('Working','Broken','Problems','Notice');

if (file_exists("config.local.php")) {
	  include("config.local.php");
}

if (!isset($dbstring)) {
		$dbstring="host=$DB_HOST port=$DB_PORT dbname=$DB_NAME user=$DB_USER password=$DB_PASSWORD";
}

?>
