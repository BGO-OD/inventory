<?php


include 'functions.php';


$typesel="";
if (isset($_GET['condition'])) {
	$cond="condition=".$_GET['condition'];
	$condition=" WHERE ".$_GET['condition'];
	if (strpos($condition,"type")!==FALSE) {
		$condparts=explode("'",$condition);
		$typesel=$condparts[1];
	}
 } else {
	$cond="";
	$condition="";
 }

page_head("Models","$PROJECT_NAME: Models");
$dbconn = pg_connect($dbstring);
if (!$dbconn) {
	  die('Could not connect: ' . pg_last_error());
};

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$query="INSERT INTO models (type,manufacturer,name,maintenance_interval,maintenance_instructions,sublocations,description,comment) VALUES (";
	$query.="'". pg_escape_string($dbconn,$_POST['type']) . "', ";
	$query.="'". pg_escape_string($dbconn,$_POST['manufacturer']) . "', ";
	$query.="'". pg_escape_string($dbconn,$_POST['name']) . "', ";
	if ($_POST['maintenance_interval']=="") {
		$query.="'10 years', ";
	} else {
		$query.="'". pg_escape_string($dbconn,$_POST['maintenance_interval']) . "', ";
	}
	$query.="'". pg_escape_string($dbconn,$_POST['maintenance_instructions']) . "', ";
	$query.="'". pg_escape_string($dbconn,$_POST['sublocations']) . "', ";
	$query.="'". pg_escape_string($dbconn,$_POST['description']) . "', ";
	$query.="'". pg_escape_string($dbconn,$_POST['comment']) . "');";
	$result=pg_query($dbconn,$query);
 }

echo '<div id=content><h1>Models</h1>';
if ($condition=="") {
	$result=pg_query($dbconn,"select type from models group by type order by type;");
	while ($row=pg_fetch_assoc($result)) {
		echo "<a href=\"models.php?condition=type='{$row['type']}'\">List of {$row['type']}s</a><br>\n";
	}
	echo "<h1>Add new model</h1>\n";
	echo "<form action=\"models.php?$cond\" method=\"post\">";
	echo "Type: <input type=\"text\" name=\"type\" size=\"20\" value=\"\"><br>";
	echo "manufacturer: <input type=\"text\" name=\"manufacturer\" size=\"20\" value=\"\"><br>";
	echo "model name: <input type=\"text\" name=\"name\" size=\"20\"><br>";
	echo "maintenance interval: <input type=\"text\" name=\"maintenance_interval\" size=\"20\"><br>";
	echo "maintenance instructions: <input type=\"textarea\" name=\"maintenance_instructions\" rows=\"5\" columns=\"50\"><br>";
	echo "sublocations: <input type=\"text\" name=\"sublocations\" size=\"60\"  value=\"\"><br>";
	echo "description: <input type=\"text\" name=\"description\" size=\"60\"  value=\"\"><br>";
	echo "comment: <input type=\"text\" name=\"comment\" size=\"60\"  value=\"\"><br>";
	echo '<input type="submit" value="Submit" >';
	echo "</form>";
} else {
	echo "<table class=\"tabletable\">\n";
	
	echo "<tr class=\"tablehead\">";
	echo "<td>model</td>";
	echo "<td>type</td>";
	echo "<td>manufacturer</td>";
	echo "<td>model name</td>";
	echo "<td>maint. interval</td>";
	echo "<td>maint. instr.</td>";
	echo "<td>sublocations</td>";
	echo "<td>description</td>";
	echo "<td>comment</td>";
	echo "<td># objects</td>";
	echo "<td># used</td>";
	echo "<td># spare</td>";
	echo "<td># problems</td>";
	echo "</tr>\n";
	
	$result = pg_query($dbconn, "SELECT *,
			(SELECT count(*) FROM (SELECT DISTINCT ON (id) id ,date,status FROM maintenance WHERE status IN ( 'Problems' , 'Broken', 'Working') AND id IN (SELECT id FROM objects WHERE objects.model=models.model) ORDER BY id ,date DESC) AS q WHERE status != 'Working' ) AS nprobs,
			(SELECT count(*) FROM maintenance WHERE status IN ( 'Problems' , 'Broken') AND id IN (SELECT id FROM objects WHERE objects.model=models.model)) AS ntotalprobs,
			(SELECT count(*) FROM objects WHERE objects.model=models.model) as nobjs,
			(SELECT count(*) FROM objects INNER JOIN usage USING (id) WHERE objects.model=models.model AND now() between validfrom AND validto AND userid=36) as nspares,
			(SELECT count(*) FROM objects INNER JOIN usage USING (id) WHERE objects.model=models.model AND now() between validfrom AND validto AND userid!=36) as nused
			FROM models $condition ORDER BY model DESC;");
	while ($row=pg_fetch_assoc($result)) {
		echo "<tr class=\"tablerow\">";
		echo "<td><a href=\"model.php?model={$row['model']}\">{$row['model']}</a></td>";
		echo "<td>{$row['type']}</td>";
		echo "<td><a href=\"models.php?condition=manufacturer='{$row['manufacturer']}'\">{$row['manufacturer']}</a></td>";
		echo "<td>{$row['name']}</td>";
		echo "<td>{$row['maintenance_interval']}</td>";
		echo "<td>{$row['maintenance_instructions']}</td>";
		echo "<td>{$row['sublocations']}</td>";
		echo "<td>{$row['description']}</td>";
		echo "<td>{$row['comment']}</td>";
		echo "<td>{$row['nobjs']}</td>";
		echo "<td>{$row['nused']}</td>";
		echo "<td>{$row['nspares']}</td>";
		if ($row['nprobs']==0) {
			$state="good";
		} else {
			$state="bad";
		}
		echo "<td class=\"$state\">{$row['nprobs']} ({$row['ntotalprobs']})</td>";
		echo "</tr>\n";
	}
	echo "</table>\n";
	
	echo "<h1>Add new model</h1>\n";
	echo "<form action=\"models.php?$cond\" method=\"post\">";
	echo "Type: <SELECT name=\"type\">\n";
	$result=pg_query($dbconn,"select type from models group by type order by type;");
	while ($row=pg_fetch_assoc($result)) {
		if ($typesel==$row['type']) {
			$sel="selected";
		} else {
			$sel="";
		}
		echo "<OPTION $sel>" . $row['type'] . "</OPTION>\n";
	}
	echo "</SELECT><br>\n";
	echo "manufacturer: <input type=\"text\" name=\"manufacturer\" size=\"20\" value=\"\"><br>";
	echo "model name: <input type=\"text\" name=\"name\" size=\"20\"><br>";
	echo "maintenance interval: <input type=\"text\" name=\"maintenance_interval\" size=\"20\"><br>";
	echo "maintenance instructions: <input type=\"textarea\" name=\"maintenance_instructions\" rows=\"5\" columns=\"50\"><br>";
	echo "sublocations: <input type=\"text\" name=\"sublocations\" size=\"60\"  value=\"\"><br>";
	echo "description: <input type=\"text\" name=\"description\" size=\"60\"  value=\"\"><br>";
	echo "comment: <input type=\"text\" name=\"comment\" size=\"60\"  value=\"\"><br>";
	echo '<input type="submit" value="Submit" >';
	echo "</form>";
 }
echo "</div>";

page_foot();
?>
