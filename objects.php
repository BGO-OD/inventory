<?php

include '../common/page_functions.php';
include 'functions.php';
include 'variables.php';
$enable_location_select=true;

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
	$location=$_POST['location'];
  $result=pg_query($dbconn,"SELECT * FROM models WHERE model={$_POST['model']};");
	$modeldata=pg_fetch_assoc($result);
	$condition=" WHERE type='{$modeldata['type']}'";


	if ($_POST['added']=="") {
		$query="INSERT INTO objects (ownerid,model,serial,location,location_description,institute_inventory_number,order_number,object_name,comment) VALUES (";
	} else {
		$query="INSERT INTO objects (added,ownerid,model,serial,location,location_description,institute_inventory_number,order_number,object_name,comment) VALUES (";
		$query.="'{$_POST['added']}', ";
	}
	$query.="'{$_POST['ownerid']}', ";
	$query.="{$_POST['model']}, ";
	$query.="'".pg_escape_string($dbconn,$_POST['serial'])."', ";
	$query.="$location, ";
	$query.="'".pg_escape_string($dbconn,$_POST['location_description'])."', ";
	$query.="'".pg_escape_string($dbconn,$_POST['institute_inventory_number'])."', ";
	$query.="'".pg_escape_string($dbconn,$_POST['order_number'])."', ";
	$query.="'".pg_escape_string($dbconn,$_POST['object_name'])."', ";
	$query.="'".pg_escape_string($dbconn,$_POST['comment'])."') RETURNING id;";
	$result=pg_query($dbconn,$query);
	if (pg_num_rows($result)!=1) {
		echo "<div id=content><h1>Add failed</h1>";
	}

 }

echo "<div id=content><h1>Objects";
$modelselcond="";
if (strpos($condition,"type")!==FALSE) {
	$condparts=explode("'",$condition);
	$type=$condparts[1];
	$modelselcond="type='$type'";
	echo " of type $type";
 } else {
	if (strpos($condition,"models.name")!==FALSE) {
		$condparts=explode("'",$condition);
		$model=$condparts[1];
		$modelselcond="models.name='$model'";
	} else if (strpos($condition,"model")!==FALSE) {
		$condparts=explode("'",$condition);
		$model=$condparts[1];
		$modelselcond="model='$model'";
	}
	
	$type="no type";
 }
echo "</h1>";

if ($condition=="") {
	$result=pg_query($dbconn,"select type from models group by type order by type;");
	while ($row=pg_fetch_assoc($result)) {
		echo "<a href=\"objects.php?condition=type='{$row['type']}'\">List of {$row['type']}s</a><br>\n";
	}
 } else if (strpos("Board,VME Module,HV Module,NIM Module,CAMAC Module",$type)!==FALSE) {
	$result=pg_query($dbconn,"SELECT * FROM models WHERE type='$type';");
	while ($row=pg_fetch_assoc($result)) {
		echo "<a href=\"objects.php?condition=model='{$row['model']}'\">List of {$row['manufacturer']} {$row['name']}</a> {$row['description']}<br>\n";
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
	
	$result = pg_query($dbconn, "SELECT id,manufacturer,models.name,serial,location,objects.comment,model,type,users.name as username,userid,object_name FROM ((objects INNER JOIN models  USING (model) ) LEFT OUTER JOIN ( (SELECT id,userid FROM usage WHERE validfrom<now() AND validto>now()) as usage NATURAL INNER JOIN users ) USING (id)) LEFT OUTER JOIN owners USING (ownerid) $condition ORDER BY id DESC;");
	while ($row=pg_fetch_assoc($result)) {
		echo "<tr class=\"rundbrun\">";
		echo "<td><a href=\"object.php?object='".$row['id']."'\">".$row['id']."</a></td>";
		
		echo "<td><a href=\"models.php?condition=type='".$row['type']."'\">".$row['type']."</a></td>";
		echo "<td><a href=\"models.php?condition=manufacturer='".$row['manufacturer']."'\">".$row['manufacturer']."</a></td>";
		echo "<td><a href=\"model.php?model=".$row['model']."\">".$row['name']."</a></td>";
		echo "<td>{$row['object_name']}</td>";
		echo "<td>{$row['serial']}</td>";
		echo "<td>".get_location($dbconn,$row['id'])."</td>";
		echo "<td><a href=\"objects.php?condition=userid={$row['userid']}\">{$row['username']}</a></td>";
		echo "<td>{$row['comment']}</td>";
		echo "</tr>\n";
	}
	echo "</table>\n";
	
	if ($modelselcond != "") {
		echo "<h2>Add new Object</h2>\n";
		
		$result = pg_query($dbconn, "SELECT location,ownerid FROM objects ORDER BY id DESC LIMIT 1;");
		while ($row=pg_fetch_assoc($result)) {
			$last_location=$row['location'];
			$last_owner=$row['ownerid'];
		}
		
		echo "<form action=\"objects.php\" method=\"post\">";
		echo "Type: <SELECT name=\"model\">\n";
		$result = pg_query($dbconn, "SELECT * FROM models WHERE $modelselcond;");
		while ($row=pg_fetch_assoc($result)) {
			echo "<OPTION value=\"{$row['model']}\">{$row['type']} {$row['manufacturer']} {$row['name']}</OPTION>\n";
		}
		echo "</SELECT><br>\n";
		echo "added on: <input type=\"text\" name=\"added\" size=\"20\" value=\"\"> YYYY-MM-DD <br>\n";
		echo "owner: ";
		select_owner($dbconn,$last_owner);echo "<br>\n";
		echo "serial: <input type=\"text\" name=\"serial\" size=\"20\"><br>\n";
		echo "object_name: <input type=\"text\" name=\"object_name\" size=\"20\"><br>\n";
		echo "Location: ";
		
		select_location('',$last_location);
		echo "<br/>";
		echo "institute inventory: <input type=\"text\" name=\"institute_inventory_number\" size=\"60\"  value=\"\"><br>\n";
		echo "order number: <input type=\"text\" name=\"order_number\" size=\"60\"  value=\"\"><br>\n";
		echo "comment: <input type=\"text\" name=\"comment\" size=\"60\"  value=\"\"><br>\n";
		echo '<input type="submit" value="Submit" >';
		echo "</form>";
	}
 }
echo "</div>";
page_foot();
?>
		
