<?php

include 'variables.php';

$dbconn = pg_connect($dbstring);
if (!$dbconn) {
	  die('Could not connect: ' . pg_last_error());
};

$location=$_GET['location'];

$location_list=array();

while($location != "") {
	$result = pg_query($dbconn, "SELECT parent_location FROM locations WHERE location=$location;");
	if(pg_num_rows($result) > 0) {
		$location_list[]=$location;
		$row=pg_fetch_assoc($result);
		$location=$row['parent_location'];
	} else {
		break;
	}
}

$result = pg_query($dbconn, "SELECT location,location_name FROM locations WHERE parent_location is NULL ORDER BY location_name;");

$name="";
if(count($location_list) ==0) {
	$name="location";
}
echo "<SELECT id=\"initial_location_selector\" name=\"$name\" onChange=\"javascript: nextSelectLocationBox(this)\" >\n";
echo "<OPTION value=0></OPTION>\n";
while ($row=pg_fetch_assoc($result)) {
	if(count($location_list) >0 && $row['location'] == $location_list[count($location_list)-1]) {
		$selected="selected";
	}else {
		$selected="";
	}
	echo "<OPTION value={$row['location']} $selected>{$row['location_name']}</OPTION>\n";
}
echo "</SELECT>";

for($i=count($location_list)-1; $i>=0; $i--) {
	$result = pg_query($dbconn, "SELECT location,location_name FROM locations WHERE parent_location = $location_list[$i] ORDER BY location_name;");
	if(pg_num_rows($result) > 0) {
		$name="";
		if($i ==1) {
			$name="location";
		}
		echo "&raquo;";
		echo "<SELECT  name=\"$name\" onChange=\"javascript: nextSelectLocationBox(this)\" >\n";
		echo "<OPTION value=$location_list[$i]></OPTION>\n";
		while ($row=pg_fetch_assoc($result)) {
			if($i>0 && $row['location'] == $location_list[$i-1]) {
				$selected="selected";
			}else {
				$selected="";
			}
			echo "<OPTION value={$row['location']} $selected>{$row['location_name']}</OPTION>\n";
		}
		echo "</SELECT>";
	}
}


?>
