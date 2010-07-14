<?php

include '../common/page_functions.php';
include 'functions.php';
include 'variables.php';


$object=$_GET['object'];

page_head("Object","B1 inventory: Object");
$dbconn = pg_connect($dbstring);
if (!$dbconn) {
	  die('Could not connect: ' . pg_last_error());
};

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$query="INSERT INTO maintenance (id,date,responsible,status,comment) VALUES (";
	$query.="$object, ";
	$query.="'". $_POST['date'] . "', ";
	$query.="'". $_POST['responsible'] . "', ";
	$query.="'". $_POST['status'] . "', ";
	$query.="'". $_POST['comment'] . "');";
	$result=pg_query($dbconn,$query);
 }

echo '<div id=content><h1>Objects</h1>';
echo "<table class=\"rundbtable\">\n";

echo "<tr class=\"rundbhead\">";
echo "<td>id</td>";
echo "<td>type</td>";
echo "<td>manufacturer</td>";
echo "<td>name</td>";
echo "<td>serial</td>";
echo "<td>location</td>";
echo "<td>comment</td>";
echo "</tr>\n";

$result = pg_query($dbconn, "SELECT * FROM objects INNER JOIN models USING (model) WHERE id=$object;");
while ($row=pg_fetch_assoc($result)) {
	echo "<tr class=\"rundbrun\">";
	echo "<td><a href=\"object.php?object='".$row['id']."'\">".$row['id']."</a></td>";
	
	echo "<td><a href=\"models.php?condition=type='".$row['type']."'\">".$row['type']."</a></td>";
	echo "<td><a href=\"models.php?condition=manufacturer='".$row['manufacturer']."'\">".$row['manufacturer']."</a></td>";
	echo "<td><a href=\"model.php?model=".$row['model']."\">".$row['name']."</a></td>";
	echo "<td>".$row['serial']."</td>";
	echo "<td>".get_location($dbconn,$row['location'])."</td>";
	echo "<td>".$row['objects.comment']."</td>";
	echo "</tr>\n";
 }
echo "</table>\n";

echo '<h2>Maintenances</h2>';

$result = pg_query($dbconn, "SELECT CASE WHEN (SELECT count(*) FROM maintenance WHERE id=$object) > 0 THEN (SELECT date FROM maintenance WHERE id=$object ORDER BY date DESC LIMIT 1) ELSE (SELECT added FROM objects INNER JOIN models USING (model) WHERE id=$object) END + (SELECT maintenance_interval FROM objects INNER JOIN models USING (model) WHERE id=$object) AS next_maintenance;");
$row=pg_fetch_assoc($result);
echo "Next maintenance: ".$row['next_maintenance']."\n";
echo "<form action=\"object.php?object=$object\" method=\"post\">";
echo "date: <input type=\"text\" name=\"date\" size=\"20\" value=\"now\"><br>";
echo "responsible: <input type=\"text\" name=\"responsible\" size=\"20\" value=\"\"><br>";
echo "status: <input type=\"text\" name=\"status\" size=\"20\" value=\"\"><br>";
echo "comment: <input type=\"text\" name=\"comment\" size=\"20\" value=\"\"><br>";
echo '<input type="submit" value="Submit" >';
echo "</form>";

echo "<table class=\"rundbtable\">\n";

echo "<tr class=\"rundbhead\">";
echo "<td>date</td>";
echo "<td>responsible</td>";
echo "<td>status</td>";
echo "<td>comment</td>";
echo "</tr>\n";

$result = pg_query($dbconn, "SELECT * FROM maintenance WHERE id=$object ORDER BY date DESC;");
while ($row=pg_fetch_assoc($result)) {
	echo "<tr class=\"rundbrun\">";
	echo "<td>".$row['date']."</td>";
	echo "<td>".$row['responsible']."</td>";
	echo "<td>".$row['status']."</td>";
	echo "<td>".$row['comment']."</td>";
	echo "</tr>\n";
 }
echo "</table>\n";

echo "</div>";
page_foot();
?>
