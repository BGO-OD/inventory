<?php

include '../common/page_functions.php';
include 'functions.php';
include 'variables.php';

$dbconn = pg_connect($dbstring);
if (!$dbconn) {
	  die('Could not connect: ' . pg_last_error());
};

$location=$_GET['location'];
$where=get_location($dbconn,$location);
page_head("Location","B1 inventory: Location");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (isset($_POST['comment'])) {
		$result=pg_query($dbconn,"UPDATE locations SET comment='".$_POST['comment']."' WHERE location=$location;");
	}
 }

echo '<div id=content><h1>Location</h1>';
echo "<form action=\"location.php?location=$location\" method=\"post\">";

echo "<table class=\"rundbtable\">\n";
echo "<tr class=\"rundbhead\">";
echo "<td>id</td>";
echo "<td>location</td>";
echo "<td>type</td>";
echo "<td>comment</td>";
echo "</tr>\n";
$result = pg_query($dbconn, "SELECT * FROM locations WHERE location=$location;");
while ($row=pg_fetch_assoc($result)) {
	echo "<tr class=\"rundbrun\">";
	echo "<td>".$row['location']."</td>";
	echo "<td>".get_location($dbconn,$row['location'])."</td>";
	echo "<td><a href=\"locations.php?condition=type='{$row['type']}'\">{$row['type']}</a></td>";
	echo "<td><input type=\"text\" name=\"comment\" size=\"50\" value=\"".$row['comment']."\"></td>";
	echo "</tr>\n";
 }
echo "</table>\n";
echo '<input type="submit" value="Submit" >';
echo "<h2>Objects</h2>";
echo "<table class=\"rundbtable\">\n";
echo "<tr class=\"rundbhead\">";
echo "<td>object</td>";
echo "<td>type</td>";
echo "<td>manufacturer</td>";
echo "<td>model name</td>";
echo "<td>serial</td>";
echo "<td>comment</td>";
echo "</tr>\n";
$result = pg_query($dbconn, "SELECT * FROM locations INNER JOIN (objects INNER JOIN models USING (model)) USING (location) WHERE location=$location;");
while ($row=pg_fetch_assoc($result)) {
	echo "<tr class=\"rundbrun\">";
	echo "<td><a href=\"object.php?object='".$row['id']."'\">".$row['id']."</a></td>";
	echo "<td><a href=\"models.php?condition=type='".$row['type']."'\">".$row['type']."</a></td>";

	echo "<td><a href=\"models.php?condition=manufacturer='".$row['manufacturer']."'\">".$row['manufacturer']."</a></td>";
	echo "<td><a href=\"model.php?model=".$row['model']."\">".$row['name']."<a></td>";
	echo "<td>".$row['serial']."</td>";
	echo "<td>".$row['objects.comment']."</td>";
	echo "</tr>\n";
 }
echo "</table>\n";


echo "<h1>Sub-Locations</h1>";
echo "<table class=\"rundbtable\">\n";
echo "<tr class=\"rundbhead\">";
echo "<td>id</td>";
echo "<td>location</td>";
echo "<td>type</td>";
echo "<td>comment</td>";
echo "<td>type</td>";
echo "<td>manufacturer</td>";
echo "<td>model name</td>";
echo "<td>id</td>";
echo "</tr>\n";
$result = pg_query($dbconn, "SELECT location,locations.type AS loctype ,locations.comment,models.type AS modtype ,manufacturer,name,id FROM locations LEFT OUTER JOIN (objects INNER JOIN models USING (model)) USING (location) WHERE parent_location=$location;");
while ($row=pg_fetch_assoc($result)) {
	echo "<tr class=\"rundbrun\">";
	echo "<td><a href=\"location.php?location={$row['location']}\">{$row['location']}</a></td>";
	echo "<td>".get_location($dbconn,$row['location'])."</td>";
	echo "<td><a href=\"locations.php?condition=type='{$row['loctype']}'\">{$row['loctype']}</a></td>";
	echo "<td>".$row['comment']."</td>";
	echo "<td>".$row['modtype']."</td>";
	echo "<td>".$row['manufacturer']."</td>";
	echo "<td>".$row['name']."</td>";
	echo "<td><a href=\"object.php?object='".$row['id']."'\">".$row['id']."</a></td>";
	echo "</tr>\n";
 }
echo "</table>\n";
echo "</form>\n";
page_foot();
?>
