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
	$location=$_POST['location'];
	$result=pg_query($dbconn,"SELECT * FROM locations WHERE location=$location;");
	$locdata=pg_fetch_assoc($result);

  $result=pg_query($dbconn,"SELECT * FROM models WHERE model={$_POST['model']};");
	$modeldata=pg_fetch_assoc($result);
	$condition=" WHERE type='{$modeldata['type']}'";

	if ($modeldata['sublocations']!="") {
		if ($locdata['type']!="Crate") {
			$result = pg_query($dbconn,"INSERT INTO locations (type,location_name,parent_location) VALUES ('Crate',(select count(*)+1 as ncrates FROM locations WHERE parent_location = $location AND type='Crate'),$location) RETURNING location;");
			$row=pg_fetch_assoc($result);
			$location=$row['location'];
		}
	}

	if ($_POST['added']=="") {
		$query="INSERT INTO objects (ownerid,model,serial,location,institute_inventory_number,order_number,object_name,comment) VALUES (";
	} else {
		$query="INSERT INTO objects (added,ownerid,model,serial,location,institute_inventory_number,order_number,object_name,comment) VALUES (";
		$query.="'{$_POST['added']}', ";
	}
	$query.="'{$_POST['ownerid']}', ";
	$query.="{$_POST['model']}, ";
	$query.="'{$_POST['serial']}', ";
	$query.="$location, ";
	$query.="'{$_POST['institute_inventory_number']}', ";
	$query.="'{$_POST['order_number']}', ";
	$query.="'{$_POST['object_name']}', ";
	$query.="'{$_POST['comment']}');";
	$result=pg_query($dbconn,$query);

	if ($modeldata['sublocations']!="") {
		if ($modeldata['sublocations']=="individual") {
		} else {
			$sublocs=explode(",",$modeldata['sublocations']);
			foreach ($sublocs as $subloc) {
				$parts=explode(" ",ltrim($subloc));
				if (strpos($parts[0],"-")===FALSE) {
					$name="";
					for ($j=0; $j<count($parts); $j++) {
						$name.=$parts[$j]." ";
					}
					create_sublocation($dbconn,$parts[count($parts)-1],$name,$location);
				} else {
					$fromto=explode("-",$parts[0]);
					for ($i=$fromto[0]; $i<=$fromto[1]; $i++) {
						$name="";
						for ($j=1; $j<count($parts); $j++) {
							$name.=$parts[$j]." ";
						}
						$name.=$i;
						create_sublocation($dbconn,$parts[count($parts)-1],$name,$location);
					}
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
	
	$result = pg_query($dbconn, "SELECT id,manufacturer,models.name,serial,location,objects.comment,model,type,users.name as username,userid,object_name FROM ((objects INNER JOIN models  USING (model) ) LEFT OUTER JOIN ( (SELECT id,userid FROM usage WHERE validfrom<now() AND validto>now()) as usage NATURAL INNER JOIN users ) USING (id)) LEFT OUTER JOIN owners USING (ownerid) $condition;");
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
			echo "<OPTION value=\"{$row['model']}\">{$row['type']} {$row['manufacturer']} {$row['name']}</OPTION>\n";
		}
		echo "</SELECT><br>\n";
		echo "added: <input type=\"text\" name=\"added\" size=\"20\" value=\"\"><br>\n";
		echo "owner: ";
		select_owner($dbconn,$row['owner_name']);echo "<br>\n";
		echo "serial: <input type=\"text\" name=\"serial\" size=\"20\"><br>\n";
		echo "object_name: <input type=\"text\" name=\"object_name\" size=\"20\"><br>\n";
		echo "Location: ";
		select_location($dbconn);
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
		
