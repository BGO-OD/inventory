<?php

include 'variables.php';

$dbconn = pg_connect($dbstring);
if (!$dbconn) {
	  die('Could not connect: ' . pg_last_error());
};

$id=$_GET['id'];
$location=$_GET['location'];

$result = pg_query($dbconn, "SELECT location_description FROM objects WHERE id=$id;");
if( $row=pg_fetch_assoc($result) ) {
	$location_description=$row['location_description'];
}



$location_list=array();

while($location != "") {
	$result = pg_query($dbconn, "SELECT location FROM objects WHERE id=$location;");
	if(pg_num_rows($result) > 0) {
		$location_list[]=$location;
		$row=pg_fetch_assoc($result);
		$location=$row['location'];
	} else {
		break;
	}
}

$result = pg_query($dbconn, "SELECT id,object_name FROM objects WHERE location is NULL ORDER BY object_name;");

$name="";
if(count($location_list) ==0) {
	$name="location";
}
echo "<SELECT id=\"initial_location_selector\" name=\"$name\" onChange=\"javascript: nextSelectLocationBox(this)\" >\n";
echo "<OPTION value=0></OPTION>\n";
while ($row=pg_fetch_assoc($result)) {
	if(count($location_list) >0 && $row['id'] == $location_list[count($location_list)-1]) {
		$selected="selected";
	}else {
		$selected="";
	}
	echo "<OPTION value={$row['id']} $selected>{$row['object_name']}</OPTION>\n";
}
echo "</SELECT>";

for($i=count($location_list)-1; $i>=0; $i--) {
	$result = pg_query($dbconn, "SELECT id, object_name, models.type, sublocations FROM objects inner join models on objects.model=models.model WHERE location = $location_list[$i] AND sublocations IS NOT NULL AND sublocations != ''  ORDER BY object_name;");
	if(pg_num_rows($result) > 0) {
		$name="";
		if($i ==1) {
			$name="location";
		}
		echo "&raquo;";
		echo "<SELECT  name=\"$name\" onChange=\"javascript: nextSelectLocationBox(this,$id)\" >\n";
		echo "<OPTION value=\"$location_list[$i]\"></OPTION>\n";
		while ($row=pg_fetch_assoc($result)) {
			
			if($i>0 && $row['id'] == $location_list[$i-1]) {
				$selected="selected";
				$sublocations=$row['sublocations'];
// 				echo "<!--$sublocations-->";
			}else {
				$selected="";
			}
			if($id== $row['id']) {
				$selected ="disabled";
			}
			$object_name=$row['object_name'];
			if($object_name == '') {
				$object_name = $row['type'].' '.$row['id'];
			}
			echo "<OPTION value=\"{$row['id']}\" $selected>$object_name</OPTION>\n";
		}
		echo "</SELECT>";
	}
}



if($sublocations!='individual') {
	echo "<br> Location description: ";
	echo "<!--$sublocations-->";
	echo "<SELECT  name=\"location_description\">\n";

	$sublocs=explode(",",$sublocations);
	foreach ($sublocs as $subloc) {
		$parts=explode(" ",ltrim($subloc));
		if (strpos($parts[0],"-")===FALSE) {
			$name="";
			for ($j=0; $j<count($parts); $j++) {
				$name.=$parts[$j]." ";
			}
			if($name == $location_description) {
				$selected="selected";
			}else {
				$selected="";
			}
			echo "<OPTION value=\"$name\" $selected>$name</OPTION>\n";

		} else {
			$fromto=explode("-",$parts[0]);
			for ($i=$fromto[0]; $i<=$fromto[1]; $i++) {
				$name="";
				for ($j=1; $j<count($parts); $j++) {
					$name.=$parts[$j]." ";
				}
				$name.=$i;
				if($name == $location_description) {
					$selected="selected";
				}else {
					$selected="";
				}
				echo "<OPTION value=\"$name\" $selected>$name</OPTION>\n";
			}
		}
	}
	echo "</SELECT>";
} else {
	echo "<br> Location description: <input type=\"text\" name=\"location_description\" size=40 value=\"$location_description\">";
}

?>
