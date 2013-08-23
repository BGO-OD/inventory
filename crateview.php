<?php

include '../common/page_functions.php';
include 'functions.php';
include 'variables.php';

$enable_location_select=true;


function list_object_at($dbconn,$object,$name) {
	$result = pg_query($dbconn,"SELECT * FROM objects WHERE location=$object AND trim(both from location_description) = '$name';");
	if (pg_num_rows($result)>0) {
		while ($row=pg_fetch_assoc($result)) {
			echo "<td>\n";
			echo "<form action=\"crateview.php?object=$object\" method=\"POST\">\n";
			echo "<input type=\"text\" name=\"subobject\" size=4 value=\"".$row['id']."\"/>\n";
			echo "<input type=\"hidden\" name=\"location\" value=\"$object\"/>\n";
			echo "<input type=\"hidden\" name=\"location_description\" value=\"$name\"/>\n";
			echo "<button name=\"submit\" type=\"submit\" value=\"submit\" >Submit</button>\n";
			echo "</form><td>\n";
		}
	} else {
		echo "<td>\n";
		echo "<form action=\"crateview.php?object=$object\" method=\"POST\">\n";
		echo "<input type=\"text\" name=\"subobject\" size=4 value=\"\"/>\n";
		echo "<input type=\"hidden\" name=\"location\" value=\"$object\"/>\n";
		echo "<input type=\"hidden\" name=\"location_description\" value=\"$name\"/>\n";
		echo "<button name=\"submit\" type=\"submit\" value=\"submit\" >Submit</button>\n";
		echo "</form><td>\n";
	}
}

$object=$_GET['object'];


page_head("B1 inventory","B1 inventory: Crate view $object");
$dbconn = pg_connect($dbstring);
if (!$dbconn) {
	  die('Could not connect: ' . pg_last_error());
};

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$subobject=$_POST['subobject'];
	$location=$_POST['location'];
	$location_description=$_POST['location_description'];
	// set location of any old object in this location to Unknown, which is object id 1988
	$query="BEGIN;";
	$query.="UPDATE objects SET location=1988 WHERE location=$location AND trim(both from location_description) = '$location_description';";
	if ($subobject!="") {
		$query.="UPDATE objects SET location=$location, location_description='$location_description' WHERE id=$subobject;";
	}
	$query.="COMMIT;";
 	$result=pg_query($dbconn,$query);
 }

echo "<div id=content><h1>Crate $object<img src=\"barcode.php?number=$object\"></h1>";

$result = pg_query($dbconn, "SELECT id,manufacturer,models.name,serial,location,objects.comment,model,type,users.name as username,object_name,usage.comment as usage_comment,institute_inventory_number,order_number,sublocations,ownerid,added,next_maintenance FROM ((objects INNER JOIN models  USING (model) ) LEFT OUTER JOIN ( (SELECT id,userid,comment FROM usage WHERE validfrom<now() AND validto>now()) as usage NATURAL INNER JOIN users ) USING (id))   LEFT OUTER JOIN owners USING (ownerid) WHERE id=$object;");
$row=pg_fetch_assoc($result);
$sublocations=$row['sublocations'];

echo "<table>\n";
if($sublocations!='individual') {
	echo "<br> Location description: ";
	echo "<!--$sublocations-->";
	$sublocs=explode(",",$sublocations);
	foreach ($sublocs as $subloc) {
		$parts=explode(" ",ltrim($subloc));
		if (strpos($parts[0],"-")===FALSE) {
			$name=ltrim($subloc);
			echo "<tr>\n";
			echo "<td>${name}</td>\n";
			list_object_at($dbconn,$object,$name);
			echo "</tr>\n";
		
		} else {
			$fromto=explode("-",$parts[0]);
			for ($i=$fromto[0]; $i<=$fromto[1]; $i++) {
				$name="";
				for ($j=1; $j<count($parts); $j++) {
					$name.=$parts[$j]." ";
				}
				$name.=$i;
				echo "<tr>\n";
				echo "<td>${name}</td>\n";
				list_object_at($dbconn,$object,$name);
				echo "</tr>\n";
			}
		}
	}
} else {
}
echo "</table>\n";


echo "</div>";
page_foot();
?>
