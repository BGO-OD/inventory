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
?>
