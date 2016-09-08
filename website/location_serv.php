<?php


include 'functions.php';

$dbconn = pg_connect($dbstring);
if (!$dbconn) {
	  die('Could not connect: ' . pg_last_error());
};

$id=$_GET['id'];
$location=$_GET['location'];

location_serv($dbconn,$id,$location);


?>
