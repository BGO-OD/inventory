<?php

include '../common/page_functions.php';
include 'functions.php';
include 'variables.php';

if (isset($_GET['condition'])) {
	$condition=" WHERE ".$_GET['condition'];
 } else {
	$condition="";
 }

page_head("Models","B1 inventory: Models");
$dbconn = pg_connect($dbstring);
if (!$dbconn) {
	  die('Could not connect: ' . pg_last_error());
};

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$query="INSERT INTO models (type,manufacturer,name,maintenance_interval,maintenance_instructions,sublocations,description,comment) VALUES (";
	$query.="'". $_POST['type'] . "', ";
	$query.="'". $_POST['manufacturer'] . "', ";
	$query.="'". $_POST['name'] . "', ";
	$query.="'". $_POST['maintenance_interval'] . "', ";
	$query.="'". $_POST['maintenance_instructions'] . "', ";
	$query.="'". $_POST['sublocations'] . "', ";
	$query.="'". $_POST['description'] . "', ";
	$query.="'". $_POST['comment'] . "');";
	$result=pg_query($dbconn,$query);
 }

echo '<div id=content><h1>Models</h1>';
if ($condition=="") {
	foreach ($model_types as $type) {
		echo "<a href=\"models.php?condition=type='$type'\">List of ${type} types</a><br>\n";
	}
 } else {
	echo "<table class=\"rundbtable\">\n";
	
	echo "<tr class=\"rundbhead\">";
	echo "<td>model</td>";
	echo "<td>type</td>";
	echo "<td>manufacturer</td>";
	echo "<td>name</td>";
	echo "<td>maintenance_interval</td>";
	echo "<td>sublocations</td>";
	echo "<td>description</td>";
	echo "<td>comment</td>";
	echo "</tr>\n";
	
	$result = pg_query($dbconn, "SELECT * FROM models $condition;");
	while ($row=pg_fetch_assoc($result)) {
		echo "<tr class=\"rundbrun\">";
		echo "<td>".$row['model']."</td>";
		echo "<td>".$row['type']."</td>";
		echo "<td>".$row['manufacturer']."</td>";
		echo "<td>".$row['name']."</td>";
		echo "<td>".$row['maintenance_interval']."</td>";
		echo "<td>".$row['sublocations']."</td>";
		echo "<td>".$row['description']."</td>";
		echo "<td>".$row['comment']."</td>";
		echo "</tr>\n";
	}
	echo "</table>\n";
	
	echo "<h1>Add new model</h1>\n";
	echo "<form action=\"models.php\" method=\"post\">";
	echo "Type: <SELECT name=\"type\">\n";
	foreach ($model_types as $type) {
		echo "<OPTION>" . $type . "</OPTION>\n";
	}
	echo "</SELECT><br>\n";
	echo "manufacturer: <input type=\"text\" name=\"manufacturer\" size=\"20\" value=\"\"><br>";
	echo "name: <input type=\"text\" name=\"name\" size=\"20\"><br>";
	echo "maintenance interval: <input type=\"text\" name=\"maintenance_interval\" size=\"20\"><br>";
	echo "maintenance instructions: <input type=\"textarea\" name=\"maintenance_instructions\" rows=\"5\" columns=\"50\"><br>";
	echo "sublocations: <input type=\"text\" name=\"sublocations\" size=\"60\"  value=\"\"><br>";
	echo "description: <input type=\"text\" name=\"description\" size=\"60\"  value=\"\"><br>";
	echo "comment: <input type=\"text\" name=\"comment\" size=\"60\"  value=\"\"><br>";
	echo '<input type="submit" value="Submit" >';
	echo "</form>";
 }
echo "</div>";

page_foot();
?>
