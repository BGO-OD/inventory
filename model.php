<?php

include '../common/page_functions.php';
include 'functions.php';
include 'variables.php';

$model=$_GET['model'];

page_head("Model","B1 inventory: Model");
$dbconn = pg_connect($dbstring);
if (!$dbconn) {
	  die('Could not connect: ' . pg_last_error());
};

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
 }

echo '<div id=content><h1>Models</h1>';
echo "<table class=\"rundbtable\">\n";

echo "<tr class=\"rundbhead\">";
echo "<td>model</td>";
echo "<td>type</td>";
echo "<td>manufacturer</td>";
echo "<td>model name</td>";
echo "<td>maintenance_interval</td>";
echo "<td>sublocations</td>";
echo "<td>description</td>";
echo "<td>comment</td>";
echo "</tr>\n";

$result = pg_query($dbconn, "SELECT * FROM models WHERE model=$model;");
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


echo '<h2>Objects </h2>';
echo "<table class=\"rundbtable\">\n";

echo "<tr class=\"rundbhead\">";
echo "<td>id</td>";
echo "<td>serial</td>";
echo "<td>location</td>";
echo "<td>comment</td>";
echo "</tr>\n";

$result = pg_query($dbconn, "SELECT * FROM models INNER JOIN objects USING (model) WHERE model=$model;");
while ($row=pg_fetch_assoc($result)) {
	echo "<tr class=\"rundbrun\">";
	echo "<td><a href=\"object.php?object='".$row['id']."'\">".$row['id']."</a></td>";
	echo "<td>".$row['serial']."</td>";
	echo "<td>".get_location($dbconn,$row['location'])."</td>";
	echo "<td>".$row['objects.comment']."</td>";
	echo "</tr>\n";
 }
echo "</table>\n";

echo "</div>";
page_foot();
?>
