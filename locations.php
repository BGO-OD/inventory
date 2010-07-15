<?php

include '../common/page_functions.php';
include 'functions.php';
include 'variables.php';

if (isset($_GET['condition'])) {
	$condition=" WHERE ".$_GET['condition'];
 } else {
	$condition="";
 }


page_head("Locations","B1 inventory: Locations");
$dbconn = pg_connect($dbstring);
if (!$dbconn) {
	  die('Could not connect: ' . pg_last_error());
};

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$query="INSERT INTO locations (type,location_name,";
	if ($_POST['parent_location']!="") {
		$query.="parent_location,";
	}
	$query.="comment) VALUES (";
	$query.="'". $_POST['type'] . "', ";
	$query.="'". $_POST['location_name'] . "', ";
	if ($_POST['parent_location']!="") {
		$query.="'". $_POST['parent_location'] . "', ";
	}
	$query.="'". $_POST['comment'] . "');";
	$result=pg_query($dbconn,$query);
 }

echo '<div id=content><h1>Locations</h1>';


if ($condition=="") {
		foreach ($location_types as $type) {
		echo "<a href=\"locations.php?condition=type='$type'\">List of ${type}s</a><br>\n";
	}

 } else {
	echo "<table class=\"rundbtable\">\n";
	
	echo "<tr class=\"rundbhead\">";
	echo "<td>id</td>";
	echo "<td>location</td>";
	echo "<td>type</td>";
	echo "<td>comment</td>";
	echo "</tr>\n";
	
	$result = pg_query($dbconn, "SELECT location,type,location_name,comment FROM locations $condition ORDER BY location_name;");
	while ($row=pg_fetch_assoc($result)) {
		echo "<tr class=\"rundbrun\">";
		echo "<td>{$row['location']}</td>";
		echo "<td>".get_location($dbconn,$row['location'])."</td>";
		echo "<td>{$row['type']}</td>";
		echo "<td>{$row['comment']}</td>";
		echo "</tr>\n";
	}
	echo "</table>\n";
	
	echo "<h1>Add new location</h1>\n";
	echo "<form action=\"locations.php\" method=\"post\">";
	echo "Type: <SELECT name=\"type\">\n";
	foreach ($location_types as $type) {
		echo "<OPTION>$type</OPTION>\n";
	}
	echo "</SELECT><br>\n";
	echo "name: <input type=\"text\" name=\"location_name\" size=\"20\" value=\"\"><br>";
	echo "parent location: <input type=\"text\" name=\"parent_location\" size=\"20\"><br>";
	echo "comment: <input type=\"text\" name=\"comment\" size=\"80\" value=\"\"><br>";
	echo '<input type="submit" value="Submit" >';
	echo "</form>";
	echo "</div>";
 }
page_foot();
?>
