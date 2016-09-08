<?php


include 'functions.php';
include 'variables.php';

$enable_location_select=true;


function list_object_at($dbconn,$object,$name) {
	$result = pg_query($dbconn,"SELECT * FROM objects WHERE location=$object AND trim(both from location_description) = trim (both from '$name');");
	if (pg_num_rows($result)>0) {
		while ($row=pg_fetch_assoc($result)) {
			echo "<td>\n";
			echo "<form action=\"crateview.php?object=$object\" method=\"POST\">\n";
			echo "<input type=\"text\" name=\"subobject\" size=4 value=\"".$row['id']."\"/>\n";
			echo "<input type=\"hidden\" name=\"location\" value=\"$object\"/>\n";
			echo "<input type=\"hidden\" name=\"location_description\" value=\"$name\"/>\n";
			echo "<button name=\"submit\" type=\"submit\" value=\"submit\" >Submit</button>\n";
			echo "</form></td>\n";
		}
	} else {
		echo "<td>\n";
		echo "<form action=\"crateview.php?object=$object\" method=\"POST\">\n";
		echo "<input type=\"text\" name=\"subobject\" size=4 value=\"\"/>\n";
		echo "<input type=\"hidden\" name=\"location\" value=\"$object\"/>\n";
		echo "<input type=\"hidden\" name=\"location_description\" value=\"$name\"/>\n";
		echo "<button name=\"submit\" type=\"submit\" value=\"submit\" >Submit</button>\n";
		echo "</form></td>\n";
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
	if (isset($_POST['location_description']) && isset($_POST['location'])) {
		$location=$_POST['location'];
		$location_description=$_POST['location_description'];
	}
	if ($_POST['submit'] == 'submit') {
		// set location of any old object in this location to Unknown, which is object id 1988
		$query="BEGIN;";
		$query.="UPDATE objects SET location=1988 WHERE location=$location AND trim(both from location_description) = '$location_description';";
		if ($subobject!="") {
			$query.="UPDATE objects SET location=$location, location_description='$location_description' WHERE id=$subobject;";
		}
		$query.="COMMIT;";
		$result=pg_query($dbconn,$query);
	} else if ($_POST['submit'] == 'movetounknown') {
		$query="UPDATE objects SET location = 1988 WHERE id = $subobject;";
		$result=pg_query($dbconn,$query);
	} else if ($_POST['submit'] == 'add') {
		$query="UPDATE objects SET location = $location, location_description='$location_description' WHERE id = $subobject;";
		$result=pg_query($dbconn,$query);
	}
 }


$result = pg_query($dbconn, "SELECT id,manufacturer,name,model,type,object_name, sublocations FROM objects INNER JOIN models  USING (model) WHERE id=$object;");
$row=pg_fetch_assoc($result);

echo "<div id=content><h1>Crate view of ${row['type']} ${row['object_name']} $object</h1>";
echo "Which is a ${row['manufacturer']} ${row['name']}<br>\n";

$sublocations=$row['sublocations'];


echo "<table>\n";
if($sublocations!='individual') {
	echo "<tr><th>Sublocation (Slot)</th><th>Object Id</th></tr>\n";
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
	echo "<tr><th>Location description</th><th>Object Id</th><th>Object type</th></tr>\n";
	$result = pg_query($dbconn,"SELECT id, location_description, type, manufacturer, name FROM objects INNER JOIN models ON objects.model = models.model WHERE location=$object ORDER BY location, id;");
	while ($row=pg_fetch_assoc($result)) {
		echo "<tr>\n";
		echo "<td>${row['location_description']}</td>\n";
		echo "<td>\n";
		echo "<form action=\"crateview.php?object=$object\" method=\"POST\">\n";
		echo "<input type=\"hidden\" name=\"subobject\" value=\"${row['id']}\"/>\n";
		echo "<a href=\"object.php?object=${row['id']}\">${row['id']}</a>";
		echo "<button name=\"submit\" type=\"submit\" value=\"movetounknown\" >Move to Unknown</button>\n";
		echo "</form></td>\n";
		echo "<td>\n";
		echo "${row['type']} ${row['manufacturer']} ${row['name']}\n";
		echo "</td>\n";
		echo "</tr>\n";
	}
		echo "<tr>\n";
		echo "<td>\n";
		echo "<form action=\"crateview.php?object=$object\" method=\"POST\">\n";
		echo "<input type=\"text\" name=\"location_description\" size=40 value=\"\"/>\n";
		echo "</td>\n";
		echo "<td>\n";
		echo "<input type=\"text\" name=\"subobject\" size=4 value=\"\"/>\n";
		echo "<input type=\"hidden\" name=\"location\" value=\"$object\"/>\n";
		echo "<button name=\"submit\" type=\"submit\" value=\"add\" >Submit</button>\n";
		echo "</td>\n";
		echo "</form>\n";
		echo "</tr>\n";
}
echo "</table>\n";


echo "</div>";
page_foot();
?>
