<?php

include '../common/page_functions.php';
include 'functions.php';
include 'variables.php';

page_head("Users","B1 inventory: Users");
$dbconn = pg_connect($dbstring);
if (!$dbconn) {
	  die('Could not connect: ' . pg_last_error());
};

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$query="INSERT INTO users (name) VALUES (";
	$query.="'{$_POST['name']}');";
	$result=pg_query($dbconn,$query);
 }

echo '<div id=content><h1>Users</h1>';
echo "<table class=\"rundbtable\">\n";

echo "<tr class=\"rundbhead\">";
echo "<td>user id</td>";
echo "<td>name</td>";
echo "<td># objects</td>";
echo "</tr>\n";

$result = pg_query($dbconn, "SELECT *, (SELECT count(*) from usage WHERE usage.userid=users.userid AND now() between validfrom and validto ) as count FROM users ORDER BY split_part(name,' ',2);");
while ($row=pg_fetch_assoc($result)) {
	echo "<tr class=\"rundbrun\">";
	echo "<td>{$row['userid']}</td>";
	echo "<td><a href=\"objects.php?condition=userid={$row['userid']}\">{$row['name']}</a></td>";
	echo "<td>{$row['count']}</td>";
	echo "</tr>\n";
 }
echo "</table>\n";

echo "<h2>Add new user</h2>\n";
echo "<form action=\"users.php\" method=\"post\">";
echo "User name: <input type=\"text\" name=\"name\" size=\"80\"><br>";
echo '<input type="submit" value="Submit" >';
echo "</form>";
echo "</div>";
page_foot();
?>
