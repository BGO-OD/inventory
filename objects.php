<?php

include '../common/page_functions.php';
include 'functions.php';
include 'variables.php';

if (isset($_GET['condition'])) {
	$condition=" WHERE ".$_GET['condition'];
 } else {
	$condition="";
 }

function create_sublocation($dbconn,$type,$name,$parent) {
	$result=pg_query($dbconn,"INSERT INTO locations (type,location_name,parent_location) VALUES ('$type','$name',$parent);");
}



page_head("Objects","B1 inventory: Objects");
$dbconn = pg_connect($dbstring);
if (!$dbconn) {
	  die('Could not connect: ' . pg_last_error());
};

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$query="INSERT INTO objects (owner,added,model,serial,location,institute_inventory_number,object_name,comment) VALUES (";
	$query.="'{$_POST['owner']}', ";
	$query.="'{$_POST['added']}', ";
	$query.="{$_POST['model']}, ";
	$query.="'{$_POST['serial']}', ";
	$query.="{$_POST['location']}, ";
	$query.="'{$_POST['institute_inventory_number']}', ";
	$query.="'{$_POST['object_name']}');";
	$query.="'{$_POST['comment']}');";
	$result=pg_query($dbconn,$query);
  $result=pg_query($dbconn,"SELECT * FROM models WHERE model='".$modelarray[0]."';");
	$row=pg_fetch_assoc($result);
	$condition=" WHERE type='{$row['type']}'";
	if ($row['sublocations']!="") {
		$sublocs=explode(",",$row['sublocations']);
		foreach ($sublocs as $subloc) {
			$parts=explode(" ",ltrim($subloc));
			if (strpos($parts[0],"-")===FALSE) {
				create_sublocation($dbconn,$parts[1],$parts[0],$_POST['location']);
			} else {
				$fromto=explode("-",$parts[0]);
				for ($i=$fromto[0]; $i<=$fromto[1]; $i++) {
					create_sublocation($dbconn,$parts[1],$i,$_POST['location']);
				}
			}
		}
	}
 }


echo "<div id=content><h1>Objects";
if (strpos($condition,"type")!==FALSE) {
	$condparts=explode("'",$condition);
	$type=$condparts[1];
	echo " of type $type";
 }
echo "</h1>";

if ($condition=="") {
	foreach ($model_types as $type) {
		echo "<a href=\"objects.php?condition=type='$type'\">List of ${type}s</a><br>\n";
	}
 } else {
	echo "<table class=\"rundbtable\">\n";
	
	echo "<tr class=\"rundbhead\">";
	echo "<td>id</td>";
	echo "<td>type</td>";
	echo "<td>manufacturer</td>";
	echo "<td>model name</td>";
	echo "<td>object name</td>";
	echo "<td>serial</td>";
	echo "<td>location</td>";
	echo "<td>used by</td>";
	echo "<td>comment</td>";
	echo "</tr>\n";
	
	$result = pg_query($dbconn, "SELECT id,manufacturer,models.name,serial,location,objects.comment,model,type,users.name as username,userid,object_name FROM (objects INNER JOIN models  USING (model) ) LEFT OUTER JOIN ( (SELECT id,userid FROM usage WHERE validfrom<now() AND validto>now()) as usage NATURAL INNER JOIN users ) USING (id) $condition;");
	while ($row=pg_fetch_assoc($result)) {
		echo "<tr class=\"rundbrun\">";
		echo "<td><a href=\"object.php?object='".$row['id']."'\">".$row['id']."</a></td>";
		
		echo "<td><a href=\"models.php?condition=type='".$row['type']."'\">".$row['type']."</a></td>";
		echo "<td><a href=\"models.php?condition=manufacturer='".$row['manufacturer']."'\">".$row['manufacturer']."</a></td>";
		echo "<td><a href=\"model.php?model=".$row['model']."\">".$row['name']."</a></td>";
		echo "<td>{$row['object_name']}</td>";
		echo "<td>{$row['serial']}</td>";
		echo "<td>".get_location($dbconn,$row['location'])."</td>";
		echo "<td><a href=\"objects.php?condition=userid={$row['userid']}\">{$row['username']}</a></td>";
		echo "<td>{$row['comment']}</td>";
		echo "</tr>\n";
	}
	echo "</table>\n";
	
	if (strpos($condition,"type")!==FALSE) {
		$condparts=explode("'",$condition);
		$type=$condparts[1];
		echo "<h2>Add new $type</h2>\n";
		echo "<form action=\"objects.php\" method=\"post\">";
		echo "Type: <SELECT name=\"model\">\n";
		$result = pg_query($dbconn, "SELECT * FROM models WHERE type='$type';");
		while ($row=pg_fetch_assoc($result)) {
			echo "<OPTION value=\"{$row['model']}\">{$row['type']} {$row['manufacturer']} {$row['name']})</OPTION>\n";
		}
		echo "</SELECT><br>\n";
		echo "added: <input type=\"text\" name=\"added\" size=\"20\" value=\"\"><br>";
		echo "owner: <input type=\"text\" name=\"owner\" size=\"20\"><br>";
		echo "serial: <input type=\"text\" name=\"serial\" size=\"20\"><br>";
		echo "object_name: <input type=\"text\" name=\"object_name\" size=\"20\"><br>";
		echo "Location: ";
		select_location($dbconn);
		echo "<br/>";
		echo "institute inventory: <input type=\"text\" name=\"institute_inventory_number\" size=\"60\"  value=\"\"><br>";
		echo "comment: <input type=\"text\" name=\"comment\" size=\"60\"  value=\"\"><br>";
		echo '<input type="submit" value="Submit" >';
		echo "</form>";
	}
 }
echo "</div>";
page_foot();
?>
		
