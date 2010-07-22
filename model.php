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
 switch ($_POST['submit']) {
	case 'update maintenance_interval':
	case 'update comment':
	case 'update maintenance_instructions':
	case 'update sublocations':
	case 'update description':
		$submitparts=explode(" ",$_POST['submit']);
		$field=$submitparts[1];
		$query="UPDATE objects SET $field='{$_POST[$field]}' WHERE id=$object;";
		$result=pg_query($dbconn,$query);
		break;
 }
}

echo "<div id=content><h1>Model $model</h1>\n";

$result = pg_query($dbconn, "SELECT * FROM models WHERE model=$model;");
$row=pg_fetch_assoc($result);

echo "<form action=\"model.php?model=$model\" method=\"POST\">\n";
echo "<table class=\"rundbtable\">\n";

echo "<tr><td>model id</td>";
echo "<td>{$row['model']}</td>";
echo "<td></td></tr>\n"; 


echo "<tr><td>type</td>";
echo "<td><a href=\"models.php?condition=type='".$row['type']."'\">".$row['type']."</a></td>";
echo "<td></td></tr>\n"; 

echo "<tr><td>manufacturer</td>";
echo "<td><a href=\"models.php?condition=manufacturer='".$row['manufacturer']."'\">".$row['manufacturer']."</a></td>";
echo "<td></td></tr>\n"; 

echo "<tr><td>model</td>";
echo "<td><a href=\"model.php?model=".$row['model']."\">".$row['name']."</a></td>";
echo "<td></td></tr>\n"; 

echo "<tr><td>maintenence interval</td>";
echo "<td><input type=\"text\" name=\"maintenance_interval\" size=30 value=\"${row['maintenance_interval']}\"></td>\n";
echo "<td><button name=\"submit\" type=\"submit\" value=\"update maintenance_interval\" >Update</button></td></tr>\n";

echo "<tr><td>maintenence instructions</td>";
echo "<td><input type=\"text\" name=\"maintenance_interval\" size=60 value=\"${row['maintenance_interval']}\"></td>\n";
echo "<td><button name=\"submit\" type=\"submit\" value=\"update maintenance_interval\" >Update</button></td></tr>\n";

echo "<tr><td>comment</td>";
echo "<td><input type=\"text\" name=\"comment\" size=60 value=\"${row['comment']}\"></td>\n";
echo "<td><button name=\"submit\" type=\"submit\" value=\"update comment\" >Update</button></td></tr>\n";

echo "<tr><td>sublocations</td>";
echo "<td><input type=\"text\" name=\"sublocations\" size=60 value=\"${row['sublocations']}\"></td>\n";
echo "<td><button name=\"submit\" type=\"submit\" value=\"update sublocations\" >Update</button></td></tr>\n";

echo "<tr><td>description</td>";
echo "<td><input type=\"text\" name=\"description\" size=60 value=\"${row['description']}\"></td>\n";
echo "<td><button name=\"submit\" type=\"submit\" value=\"update description\" >Update</button></td></tr>\n";



echo "</table>\n";
echo "</form>\n";



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
