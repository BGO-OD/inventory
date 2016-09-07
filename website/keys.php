<?php

include '../common/page_functions.php';
include 'functions.php';
include 'variables.php';

page_head("Keys","B1 inventory: Keys");
$dbconn = pg_connect($dbstring);
if (!$dbconn) {
	  die('Could not connect: ' . pg_last_error());
};

echo "\n".'<div id=content>'."\n\t".'<h1>Guest Keys HSAG</h1>'."\n";
echo "\t<table class=\"rundbtable\">\n";

echo "\t\t<tr class=\"rundbhead\">";
echo "<td>Mechanical Key Nr.</td>";
echo "<td colspan=\"2\">Electronic Key Nr.</td>";
echo "<td rowspan=\"2\">Used by:</td>";
echo "</tr>\n";
echo "\t\t<tr class=\"rundbhead\">";
echo "<td>Office (HR7)</td>";
echo "<td>With Hall Access</td>";
echo "<td>No Hall Access</td>";
echo "</tr>\n";

$result = pg_query($dbconn, "
	SELECT
		id,
		serial,
		(SELECT users.name FROM users, usage WHERE users.userid=usage.userid AND usage.id=p.id AND usage.validfrom<now() AND usage.validto>now()) as user,
		(SELECT serial FROM objects where location=p.id and comment='hall access') as withhallaccess,
		(SELECT id FROM objects where location=p.id and comment='hall access') as withhallaccessid,
		(SELECT serial FROM objects where location=p.id and comment='without hall access') as withouthallaccess,
		(SELECT id FROM objects where location=p.id and comment='without hall access') as withouthallaccessid
	FROM objects as p
	WHERE model=299
	ORDER BY serial::int;
	");
while ($row=pg_fetch_assoc($result)) {
	echo "\t\t<tr class=\"rundbrun\">";
	echo "<td><a href=\"object.php?object={$row['id']}\">{$row['serial']}</a></td>";
	echo "<td><a href=\"object.php?object={$row['withhallaccessid']}\">{$row['withhallaccess']}</a></td>";
	echo "<td><a href=\"object.php?object={$row['withouthallaccessid']}\">{$row['withouthallaccess']}</a></td>";
	echo "<td>{$row['user']}</td>";
	echo "</tr>\n";
}

echo "\t\t<tr class=\"rundbhead\">";
echo "<td>Office (HR6)</td>";
echo "<td>With Hall Access</td>";
echo "<td>No Hall Access</td>";
echo "<td>Used by:</td>";
echo "</tr>\n";

$result = pg_query($dbconn, "
	SELECT
		id,
		serial,
		(SELECT users.name FROM users, usage WHERE users.userid=usage.userid AND usage.id=p.id AND usage.validfrom<now() AND usage.validto>now()) as user,
		(SELECT serial FROM objects where location=p.id and comment='hall access') as withhallaccess,
		(SELECT id FROM objects where location=p.id and comment='hall access') as withhallaccessid,
		(SELECT serial FROM objects where location=p.id and comment='without hall access') as withouthallaccess,
		(SELECT id FROM objects where location=p.id and comment='without hall access') as withouthallaccessid
	FROM objects as p
	WHERE model=300
	ORDER BY serial::int;
	");
while ($row=pg_fetch_assoc($result)) {
	echo "\t\t<tr class=\"rundbrun\">";
	echo "<td><a href=\"object.php?object={$row['id']}\">{$row['serial']}</a></td>";
	echo "<td><a href=\"object.php?object={$row['withhallaccessid']}\">{$row['withhallaccess']}</a></td>";
	echo "<td><a href=\"object.php?object={$row['withouthallaccessid']}\">{$row['withouthallaccess']}</a></td>";
	echo "<td>{$row['user']}</td>";
	echo "</tr>\n";
}

echo "\t\t<tr class=\"rundbhead\">";
echo "<td></td>";
echo "<td>With Hall Access</td>";
echo "<td>No Hall Access</td>";
echo "<td>Used by:</td>";
echo "</tr>\n";

$result = pg_query($dbconn, "
	SELECT
		id,
		serial,
		(SELECT users.name FROM users, usage WHERE users.userid=usage.userid AND usage.id=p.id AND usage.validfrom<now() AND usage.validto>now()) as user,
		comment
	FROM objects as p
	WHERE
		(SELECT model FROM objects WHERE id=p.location) != 300 AND
		(SELECT model FROM objects WHERE id=p.location) != 299 AND
		model=301
	ORDER BY serial;
	");
while ($row=pg_fetch_assoc($result)) {
	echo "\t\t<tr class=\"rundbrun\">";
	echo "<td></td>";
	if ($row['comment'] == "hall access") echo "<td><a href=\"object.php?object={$row['id']}\">{$row['serial']}</a></td>"; else echo "<td></td>";
	if ($row['comment'] == "without hall access") echo "<td><a href=\"object.php?object={$row['id']}\">{$row['serial']}</a></td>"; else echo "<td></td>";
	echo "<td>{$row['user']}</td>";
	echo "</tr>\n";
}

echo "\t\t<tr class=\"rundbhead\">";
echo "<td colspan=\"3\">Wesselhalle (1/4)</td>";
echo "<td>Used by:</td>";
echo "</tr>\n";

$result = pg_query($dbconn, "
	SELECT 
		(SELECT users.name FROM users, usage WHERE users.userid=usage.userid AND usage.id=p.id AND usage.validfrom<now() AND usage.validto>now()) as user,
		serial,
		id
	FROM objects as p
	WHERE model=302
	ORDER BY serial::int;");
while ($row=pg_fetch_assoc($result)) {
	echo "\t\t<tr class=\"rundbrun\">";
	echo "<td colspan=\"3\"><a href=\"object.php?object={$row['id']}\">{$row['serial']}</a></td>";
	echo "<td>{$row['user']}</td>";
	echo "</tr>\n";
}

echo "\t</table>\n";

echo "</div>\n\n";
page_foot();
?>
