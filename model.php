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
echo "<td>object name</td>";
echo "<td>location</td>";
echo "<td>used by</td>";
echo "<td>comment</td>";
echo "</tr>\n";

$result = pg_query($dbconn, "SELECT id,serial,object_name,location,objects.comment,users.name as username,userid FROM (objects INNER JOIN models  USING (model) ) LEFT OUTER JOIN ( (SELECT id,userid FROM usage WHERE validfrom<now() AND validto>now()) as usage NATURAL INNER JOIN users ) USING (id) WHERE model=$model;");
while ($row=pg_fetch_assoc($result)) {
	echo "<tr class=\"rundbrun\">";
	echo "<td><a href=\"object.php?object='{$row['id']}'\">{$row['id']}</a></td>";
	echo "<td>{$row['serial']}</td>";
	echo "<td>{$row['object_name']}</td>";
	echo "<td>".get_location($dbconn,$row['location'])."</td>";
	echo "<td><a href=\"objects.php?condition=userid={$row['userid']}\">{$row['username']}</a></td>";
	echo "<td>{$row['comment']}</td>";
	echo "</tr>\n";
 }
echo "</table>\n";


echo '<h2>Known problems </h2>';
echo "<table class=\"rundbtable\">\n";

echo "<tr class=\"rundbhead\">";
echo "<td>id</td>";
echo "<td>object name</td>";
echo "<td>date</td>";
echo "<td>status</td>";
echo "<td>comment</td>";
echo "</tr>\n";

$result = pg_query($dbconn, "SELECT id, object_name, date, status, maintenance.comment FROM objects INNER JOIN maintenance  USING (id) WHERE model=$model AND (status='Broken' OR status='Problem');");
while ($row=pg_fetch_assoc($result)) {
	echo "<tr class=\"rundbrun\">";
	echo "<td><a href=\"object.php?object='{$row['id']}'\">{$row['id']}</a></td>";
	echo "<td>{$row['object_name']}</td>";
	echo "<td>{$row['date']}</td>";
	echo "<td>{$row['status']}</td>";
	echo "<td>{$row['comment']}</td>";
	echo "</tr>\n";
 }
echo "</table>\n";



echo "</div>";
page_foot();
?>
