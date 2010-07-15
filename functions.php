<?php

function get_location($dbconn,$location,$with_links=TRUE) {
	$string="";
	do {
		$result=pg_query($dbconn,"SELECT parent_location,type,location_name FROM locations WHERE location='$location';");
		if ($row=pg_fetch_assoc($result)) {
			if ($string!="") {
				$string="&raquo;".$string;
			}
			if ($with_links) {
				$string="</a>".$string;
			}
			$string=$row['location_name'].$string;
			if ($with_links) {
				$string="<a href=\"location.php?location=$location\">".$string;
			}
			$location=$row['parent_location'];
		} else {
			break;
		}
	} while ($location!=NULL);
	return $string;
	}

function navigation_bar() {
	echo "<DIV id=\"navigation\">\n";
	echo "<a class=\"navbutton\" href=\"locations.php\">Locations list</a>\n";
	echo "<a class=\"navbutton\" href=\"models.php\">Models list</a>\n";
	echo "<a class=\"navbutton\" href=\"objects.php\">Objects list</a>\n";
	echo "</DIV>\n";
}
?>
