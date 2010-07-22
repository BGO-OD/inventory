<?php

include '../common/page_functions.php';
include 'functions.php';
include 'variables.php';


$object=$_GET['object'];

page_head("Object $object","B1 inventory: Object");
$dbconn = pg_connect($dbstring);
if (!$dbconn) {
	  die('Could not connect: ' . pg_last_error());
};

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	switch ($_POST['submit']) {
	case 'maintain':
		$query="INSERT INTO maintenance (id,date,responsible,status,comment) VALUES (";
		$query.="$object, ";
		$query.="'". $_POST['date'] . "', ";
		$query.="'". $_POST['responsible'] . "', ";
		$query.="'". $_POST['status'] . "', ";
		$query.="'". $_POST['maint_comment'] . "');";
		$result=pg_query($dbconn,$query);
		break;
	case 'update object_name':
	case 'update comment':
	case 'update serial':
	case 'update ownerid':
	case 'update institute_inventory_number':
	case 'update order_number':
		$submitparts=explode(" ",$_POST['submit']);
		$field=$submitparts[1];
		$query="UPDATE objects SET $field='{$_POST[$field]}' WHERE id=$object;";
		$result=pg_query($dbconn,$query);
		break;
	case 'update location':
	case 'update location empty':
		$result = pg_query($dbconn,"SELECT location,sublocations FROM objects INNER JOIN models  USING (model) WHERE id=$object;");
		$row=pg_fetch_assoc($result);
		$old_location=$row['location'];
		$submitparts=explode(" ",$_POST['submit']);
		switch ($submitparts[2]) {
		case 'empty':
		case 'full':
		case 'basic':
			$query="UPDATE objects SET location='{$_POST['location']}' WHERE id=$object;";
			$result=pg_query($dbconn,$query);
		}
		break;
	case 'update_user':
		$query="UPDATE usage SET validto='now()' WHERE id=$object AND validto='infinity';";
		$result=pg_query($dbconn,$query);
		$query="INSERT INTO usage (id,userid,comment) VALUES ($object,{$_POST['userid']},'{$_POST['usage_comment']}');";
		$result=pg_query($dbconn,$query);

		break;
	}
 }

echo "<div id=content><h1>Object $object<img src=\"barcode.php?number=$object\"></h1>";

$result = pg_query($dbconn, "SELECT id,manufacturer,models.name,serial,location,objects.comment,model,type,users.name as username,object_name,usage.comment as usage_comment,institute_inventory_number,order_number,sublocations,owner_name FROM ((objects INNER JOIN models  USING (model) ) LEFT OUTER JOIN ( (SELECT id,userid,comment FROM usage WHERE validfrom<now() AND validto>now()) as usage NATURAL INNER JOIN users ) USING (id))   LEFT OUTER JOIN owners USING (ownerid) WHERE id=$object;");
$row=pg_fetch_assoc($result);

echo "<form action=\"object.php?object=$object\" method=\"POST\">";

echo "<table class=\"rundbtable\">\n";

echo "<tr><td>object id</td>";
echo "<td><a href=\"object.php?object='".$row['id']."'\">".$row['id']."</a></td>";
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
		

echo "<tr><td>object name</td>";
echo "<td><input type=\"text\" name=\"object_name\" size=60 value=\"${row['object_name']}\"></td>\n";
echo "<td><button name=\"submit\" type=\"submit\" value=\"update object_name\" >Update</button></td></tr>\n";

if ($row['sublocations']!="") {
	echo "<tr><td rowspan=\"3\">location</td>   <td rowspan=\"3\">".get_location($dbconn,$row['location'])." ";
 } else {
	echo "<tr><td>location</td>   <td>".get_location($dbconn,$row['location'])." ";
 }

select_location($dbconn);
echo "</td>";
if ($row['sublocations']!="") {
	echo "<td><button name=\"submit\" type=\"submit\" value=\"update location empty\" >Move without content</button></td></tr>\n";
	echo "<td><button name=\"submit\" type=\"submit\" value=\"update location full\" >Move with all content</button></td></tr>\n";
	echo "<td><button name=\"submit\" type=\"submit\" value=\"update location basic\" >Move with power/fan</button></td></tr>\n";
 } else {
	echo "<td><button name=\"submit\" type=\"submit\" value=\"update location empty\" >Move</button></td></tr>\n";
 }
echo "<tr><td>User</td>       <td>";
select_user($dbconn,$row['username']);
echo "Comment: <input type=\"text\" name=\"usage_comment\" size=40 value=\"${row['usage_comment']}\">";
echo "</td><td><button name=\"submit\" type=\"submit\" value=\"update_user\" >Update</button></td></tr>\n";

echo "<tr><td>object comment</td>";
echo "<td><input type=\"text\" name=\"comment\" size=60 value=\"${row['comment']}\"></td>\n";
echo "<td><button name=\"submit\" type=\"submit\" value=\"update comment\" >Update</button></td></tr>\n";

echo "<tr><td>serial number</td>";
echo "<td><input type=\"text\" name=\"serial\" size=60 value=\"${row['serial']}\"></td>\n";
echo "<td><button name=\"submit\" type=\"submit\" value=\"update serial\" >Update</button></td></tr>\n";
echo "<tr><td>institute inventory number</td>";
echo "<td><input type=\"text\" name=\"institute_inventory_number\" size=60 value=\"${row['institute_inventory_number']}\"></td>\n";
echo "<td><button name=\"submit\" type=\"submit\" value=\"update institute_inventory_number\" >Update</button></td></tr>\n";
echo "<tr><td>Owner</td>       <td>";
select_owner($dbconn,$row['owner_name']);
echo "</td><td><button name=\"submit\" type=\"submit\" value=\"update ownerid\" >Update</button></td></tr>\n";
echo "<tr><td>order number</td>";
echo "<td><input type=\"text\" name=\"order_number\" size=60 value=\"${row['order_number']}\"></td>\n";
echo "<td><button name=\"submit\" type=\"submit\" value=\"update order_number\" >Update</button></td></tr>\n";



echo "</table>\n";



echo '<h2>Maintenances</h2>';

$result = pg_query($dbconn, "SELECT CASE WHEN (SELECT count(*) FROM maintenance WHERE id=$object) > 0 THEN (SELECT date FROM maintenance WHERE id=$object ORDER BY date DESC LIMIT 1) ELSE (SELECT added FROM objects INNER JOIN models USING (model) WHERE id=$object) END + (SELECT maintenance_interval FROM objects INNER JOIN models USING (model) WHERE id=$object) AS next_maintenance;");
$row=pg_fetch_assoc($result);
echo "Next maintenance: ".$row['next_maintenance']."<br>\n";
echo "<form action=\"object.php?object=$object\" method=\"post\">";
echo "date: <input type=\"text\" name=\"date\" size=\"20\" value=\"now\"><br>";
echo "responsible: <input type=\"text\" name=\"responsible\" size=\"20\" value=\"\"><br>";
echo "Type: <SELECT name=\"status\">\n";
	foreach ($maintenance_states as $state) {
		echo "<OPTION>$state</OPTION>\n";
	}
echo "</SELECT><br>\n";
echo "comment: <input type=\"text\" name=\"maint_comment\" size=\"60\" value=\"\"><br>";
echo "<button name=\"submit\" type=\"submit\" value=\"maintain\" >Enter maintenance data</button><br>\n";
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

echo "<h2>Usage history</h2>\n";

echo "<table class=\"rundbtable\">\n";
echo "<tr class=\"rundbhead\">";
echo "<td>From</td>";
echo "<td>Till</td>";
echo "<td>used by</td>";
echo "<td>comment</td>";
echo "</tr>\n";
$result = pg_query($dbconn,"SELECT * FROM usage NATURAL INNER JOIN users WHERE id=$object ORDER BY validfrom DESC;");
while ($row=pg_fetch_assoc($result)) {
	echo "<tr class=\"rundbrun\">";
	echo "<td>{$row['validfrom']}</td>";
	echo "<td>{$row['validto']}</td>";
	echo "<td>{$row['name']}</td>";
	echo "<td>{$row['comment']}</td>";
	echo "</tr>\n";
 }
echo "</table>\n";

echo "</div>";
page_foot();
?>
