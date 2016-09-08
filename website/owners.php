<?php


include 'functions.php';


page_head("Owners","$PROJECT_NAME: Owners");
$dbconn = pg_connect($dbstring);
if (!$dbconn) {
	  die('Could not connect: ' . pg_last_error());
};

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$query="INSERT INTO owners (owner_name) VALUES (";
	$query.="'{$_POST['owner_name']}');";
	$result=pg_query($dbconn,$query);
 }

echo '<div id=content><h1>Owners</h1>';
echo "<table class=\"tabletable\">\n";

echo "<tr class=\"tablehead\">";
echo "<td>owner id</td>";
echo "<td>name</td>";
echo "</tr>\n";

$result = pg_query($dbconn, "SELECT * FROM owners;");
while ($row=pg_fetch_assoc($result)) {
	echo "<tr class=\"tablerow\">";
	echo "<td>{$row['ownerid']}</td>";
	echo "<td><a href=\"objects.php?condition=ownerid={$row['ownerid']}\">{$row['owner_name']}</a></td>";
	echo "</tr>\n";
 }
echo "</table>\n";

echo "<h2>Add new owner</h2>\n";
echo "<form action=\"owners.php\" method=\"post\">";
echo "Owner name: <input type=\"text\" name=\"owner_name\" size=\"80\"><br>";
echo '<input type="submit" value="Submit" >';
echo "</form>";
echo "</div>";
page_foot();
?>
