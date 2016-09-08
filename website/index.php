<?php


include 'functions.php';


page_head($PROJECT_NAME, $PROJECT_NAME);

echo '<div id=content><h1></h1>';

echo "<h2>Next Maintenances to be performed:</h2>\n";
$dbconn = pg_connect($dbstring);
if (!$dbconn) {
	  die('Could not connect: ' . pg_last_error());
 }
$result = pg_query($dbconn, "SELECT id,manufacturer,models.name,serial,location,objects.comment,model,type,users.name as username,userid,object_name,next_maintenance,maintenance_instructions FROM ((objects INNER JOIN models  USING (model) ) LEFT OUTER JOIN ( (SELECT id,userid FROM usage WHERE validfrom<now() AND validto>now()) as usage INNER JOIN users USING (userid) ) USING (id)) LEFT OUTER JOIN owners USING (ownerid) WHERE next_maintenance IS NOT NULL ORDER BY next_maintenance LIMIT 10;");
	echo "<table class=\"tabletable\">\n";
	
	echo "<tr class=\"tablehead\">";
	echo "<td>id</td>";
	echo "<td>Next Maintenance</td>";
	echo "<td>type</td>";
	echo "<td>manufacturer</td>";
	echo "<td>model name</td>";
	echo "<td>object name</td>";
	echo "<td>serial</td>";
	echo "<td>location</td>";
	echo "<td>used by</td>";
	echo "<td>comment</td>";
	echo "<td>what to do</td>";
	echo "</tr>\n";
	while ($row=pg_fetch_assoc($result)) {
		echo "<tr class=\"tablerow\">";
		echo "<td><a href=\"object.php?object='".$row['id']."'\">".$row['id']."</a></td>";
		echo "<td>{$row['next_maintenance']}</td>";
				echo "<td><a href=\"models.php?condition=type='".$row['type']."'\">".$row['type']."</a></td>";
		echo "<td><a href=\"models.php?condition=manufacturer='".$row['manufacturer']."'\">".$row['manufacturer']."</a></td>";
		echo "<td><a href=\"model.php?model=".$row['model']."\">".$row['name']."</a></td>";
		echo "<td>{$row['object_name']}</td>";
		echo "<td>{$row['serial']}</td>";
		echo "<td>".get_location($dbconn,$row['id'])."</td>";
		echo "<td><a href=\"objects.php?condition=userid={$row['userid']}\">{$row['username']}</a></td>";
		echo "<td>{$row['comment']}</td>";
		echo "<td>{$row['maintenance_instructions']}</td>";
		echo "</tr>\n";
	}
	echo "</table>\n";


echo "<h2>Lost objects:</h2>\n";
$result = pg_query($dbconn, "SELECT id,manufacturer,models.name,serial,location,objects.comment,model,type,users.name as username,userid,object_name,next_maintenance,maintenance_instructions FROM ((objects INNER JOIN models  USING (model) ) LEFT OUTER JOIN ( (SELECT id,userid FROM usage WHERE validfrom<now() AND validto>now()) as usage INNER JOIN users USING (userid) ) USING (id)) LEFT OUTER JOIN owners USING (ownerid) WHERE (location=1988 OR location=1860) AND type!='Location' ORDER BY id DESC;");
	echo "<table class=\"tabletable\">\n";
	
	echo "<tr class=\"tablehead\">";
	echo "<td>id</td>";
	echo "<td>Next Maintenance</td>";
	echo "<td>type</td>";
	echo "<td>manufacturer</td>";
	echo "<td>model name</td>";
	echo "<td>object name</td>";
	echo "<td>serial</td>";
	echo "<td>location</td>";
	echo "<td>used by</td>";
	echo "<td>comment</td>";
	echo "<td>what to do</td>";
	echo "</tr>\n";
	while ($row=pg_fetch_assoc($result)) {
		echo "<tr class=\"tablerow\">";
		echo "<td><a href=\"object.php?object='".$row['id']."'\">".$row['id']."</a></td>";
		echo "<td>{$row['next_maintenance']}</td>";
				echo "<td><a href=\"models.php?condition=type='".$row['type']."'\">".$row['type']."</a></td>";
		echo "<td><a href=\"models.php?condition=manufacturer='".$row['manufacturer']."'\">".$row['manufacturer']."</a></td>";
		echo "<td><a href=\"model.php?model=".$row['model']."\">".$row['name']."</a></td>";
		echo "<td>{$row['object_name']}</td>";
		echo "<td>{$row['serial']}</td>";
		echo "<td>".get_location($dbconn,$row['id'])."</td>";
		echo "<td><a href=\"objects.php?condition=userid={$row['userid']}\">{$row['username']}</a></td>";
		echo "<td>{$row['comment']}</td>";
		echo "<td>{$row['maintenance_instructions']}</td>";
		echo "</tr>\n";
	}
	echo "</table>\n";


echo "</div>";
page_foot();
?>
