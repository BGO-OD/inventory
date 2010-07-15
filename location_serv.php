<?php

include 'variables.php';

$dbconn = pg_connect($dbstring);
if (!$dbconn) {
	  die('Could not connect: ' . pg_last_error());
};

$location=$_GET['location'];

$result = pg_query($dbconn, "SELECT location,location_name,type FROM locations WHERE parent_location=$location ORDER BY location_name;");
if(pg_num_rows($result) > 0) {
	echo "<OPTION value=0></OPTION>";
}
while ($row=pg_fetch_assoc($result)) {
	echo "<OPTION value={$row['location']}>{$row['type']} {$row['location_name']}</OPTION>\n";
}
?>
