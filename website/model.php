<?php


include 'functions.php';


$model=$_GET['model'];

page_head("Model","$PROJECT_NAME: Model");
$dbconn = pg_connect($dbstring);
if (!$dbconn) {
	  die('Could not connect: ' . pg_last_error());
};
echo "<div id=content>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (isset($_POST['comment'])) {
		$comment=pg_escape_string($dbconn,$_POST['comment']);
	}

	switch ($_POST['submit']) {
	case 'update maintenance_interval':
	case 'update comment':
	case 'update maintenance_instructions':
	case 'update sublocations':
	case 'update description':
		$submitparts=explode(" ",$_POST['submit']);
		$field=$submitparts[1];
		$query="UPDATE models SET $field='{$_POST[$field]}' WHERE model=$model;";
		$result=pg_query($dbconn,$query);
		break;
	case 'add model_weblink':
		if($_POST['link'] != "") {
			$result=pg_query($dbconn,"INSERT INTO model_weblinks (model,link,comment) VALUES ($model,'{$_POST['link']}','$comment'); ");
		} else {
			echo "<h2>Error: empty link</h2>";
		}
		break;
	case 'update model_weblink':
		if($_POST['link'] != "") {
			$result=pg_query($dbconn,"UPDATE model_weblinks SET link='".pg_escape_string($_POST['link'])."',comment='$comment' WHERE linkid=".pg_escape_string($_GET['linkid']).";");
		} else {
			echo "<h2>Error: empty link</h2>";
		}
		break;
	case 'add model_file':
		if($_FILES['userfile']['error'] == UPLOAD_ERR_OK) {
			pg_query($dbconn, "begin");
			$oid = pg_lo_import($dbconn,$_FILES['userfile']['tmp_name']);
			pg_query($dbconn,"INSERT INTO files (file_name,mimetype,file,size) VALUES ('{$_FILES['userfile']['name']}', '{$_FILES['userfile']['type']}', $oid ,'{$_FILES['userfile']['size']}' );");
			$result=pg_query($dbconn,"INSERT INTO model_weblinks (model,link,comment) VALUES ($model,'file.php?oid=$oid','$comment'); ");
			pg_query($dbconn, "commit");
		} else {
			echo "<h2>Error uploading file. Error Code:" . $_FILES['userfile']['error'] ."</h2>";
		}
		break;
 }
}

echo "<h1>Model $model</h1>\n";

$result = pg_query($dbconn, "SELECT * FROM models WHERE model=$model;");
$row=pg_fetch_assoc($result);

echo "<form action=\"model.php?model=$model\" method=\"POST\">\n";
echo "<table class=\"tabletable\">\n";

echo "<tr><td>model id</td>";
echo "<td>{$row['model']}</td>";
echo "<td></td></tr>\n"; 


echo "<tr><td>type</td>";
echo "<td><a href=\"models.php?condition=type='".$row['type']."'\">".$row['type']."</a></td>";
echo "<td></td></tr>\n"; 

echo "<tr><td>manufacturer</td>";
echo "<td><a href=\"models.php?condition=manufacturer='".$row['manufacturer']."'\">".$row['manufacturer']."</a></td>";
echo "<td></td></tr>\n"; 

echo "<tr><td>model</td>";
echo "<td><a href=\"model.php?model=".$row['model']."\">".$row['name']."</a></td>";
echo "<td></td></tr>\n"; 

echo "<tr><td>maintenence interval</td>";
echo "<td><input type=\"text\" name=\"maintenance_interval\" size=30 value=\"${row['maintenance_interval']}\"></td>\n";
echo "<td><button name=\"submit\" type=\"submit\" value=\"update maintenance_interval\" >Update</button></td></tr>\n";

echo "<tr><td>maintenence instructions</td>";
echo "<td><input type=\"text\" name=\"maintenance_instructions\" size=60 value=\"${row['maintenance_instructions']}\"></td>\n";
echo "<td><button name=\"submit\" type=\"submit\" value=\"update maintenance_instructions\" >Update</button></td></tr>\n";

echo "<tr><td>comment</td>";
echo "<td><input type=\"text\" name=\"comment\" size=60 value=\"${row['comment']}\"></td>\n";
echo "<td><button name=\"submit\" type=\"submit\" value=\"update comment\" >Update</button></td></tr>\n";

echo "<tr><td>sublocations</td>";
echo "<td><input type=\"text\" name=\"sublocations\" size=60 value=\"${row['sublocations']}\"></td>\n";
echo "<td><button name=\"submit\" type=\"submit\" value=\"update sublocations\" >Update</button></td></tr>\n";

echo "<tr><td>description</td>";
echo "<td><input type=\"text\" name=\"description\" size=60 value=\"${row['description']}\"></td>\n";
echo "<td><button name=\"submit\" type=\"submit\" value=\"update description\" >Update</button></td></tr>\n";



echo "</table>\n";
echo "</form>\n";



echo '<h2>Objects </h2>';
echo "<table class=\"tabletable\">\n";

echo "<tr class=\"tablehead\">";
echo "<td>id</td>";
echo "<td>serial</td>";
echo "<td>object name</td>";
echo "<td>location</td>";
echo "<td>used by</td>";
echo "<td>comment</td>";
echo "</tr>\n";

$result = pg_query($dbconn, "SELECT id,serial,object_name,location,objects.comment,users.name as username,userid FROM (objects INNER JOIN models  USING (model) ) LEFT OUTER JOIN ( (SELECT id,userid FROM usage WHERE validfrom<now() AND validto>now()) as usage INNER JOIN users USING (userid) ) USING (id) WHERE model=$model ORDER BY id;");
while ($row=pg_fetch_assoc($result)) {
	echo "<tr class=\"tablerow\">";
	echo "<td><a href=\"object.php?object='{$row['id']}'\">{$row['id']}</a></td>";
	echo "<td>{$row['serial']}</td>";
	echo "<td>{$row['object_name']}</td>";
	echo "<td>".get_location($dbconn,$row['id'])."</td>";
	echo "<td><a href=\"objects.php?condition=userid={$row['userid']}\">{$row['username']}</a></td>";
	echo "<td>{$row['comment']}</td>";
	echo "</tr>\n";
 }
echo "</table>\n";

echo "<a href=\"objects.php?condition=model='$model'\">Create an Object of this kind</a><br>\n";

echo "<h2>Documentation links</h2>\n";
$result = pg_query($dbconn,"SELECT * FROM model_weblinks WHERE model=$model ORDER BY linkid;");
echo "<table class=\"tabletable\">\n";
echo "<tr class=\"tablehead\">";
echo "<td>id</td>";
echo "<td>link</td>";
echo "<td>comment</td>";
echo "<td></td>";
echo "</tr>\n";
while ($row=pg_fetch_assoc($result)) {
	echo "<tr class=\"tablerow\">";
	echo "<form action=\"model.php?model=$model&linkid={$row['linkid']}\" method=\"POST\">\n";
	echo "<td><a href=\"{$row['link']}\">{$row['linkid']}</a></td>";
	echo "<td><input type=\"text\" name=\"link\" size=50 value=\"{$row['link']}\"></td>";
	echo "<td><input type=\"text\" name=\"comment\" size=20 value=\"{$row['comment']}\"></td>";
	echo "<td><button name=\"submit\" type=\"submit\" value=\"update model_weblink\" >Update</button></td>\n";
	echo "</form>\n";
	echo "</tr>\n";
 }
echo "<tr class=\"tablerow\">";
echo "<form action=\"model.php?model=$model\" method=\"POST\">\n";
echo "<td></td>";
echo "<td><input type=\"text\" name=\"link\" size=50 value=\"\"></td>";
echo "<td><input type=\"text\" name=\"comment\" size=20 value=\"\"></td>";
echo "<td><button name=\"submit\" type=\"submit\" value=\"add model_weblink\" >Add</button></td>\n";
echo "</form>\n";
echo "</tr>\n";
echo "<tr class=\"tablerow\">";
echo "<form enctype=\"multipart/form-data\" action=\"model.php?model=$model\" method=\"POST\">\n";
echo "    <!-- MAX_FILE_SIZE must precede the file input field -->\n";
echo "    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"30000000\" />\n";
echo '    <!-- Name of input element determines name in $_FILES array -->'."\n";
echo "<td></td>";
echo "<td>file: <input name=\"userfile\" type=\"file\" /></td>\n";
echo "<td><input type=\"text\" name=\"comment\" size=20 value=\"\"></td>";
echo "<td><button name=\"submit\" type=\"submit\" value=\"add model_file\" >Add File</button></td>\n";
echo "</form>\n";
echo "</tr>\n";
echo "</table>\n";



echo "<h2>Known problems </h2>\n";
echo "<table class=\"tabletable\">\n";

echo "<tr class=\"tablehead\">";
echo "<td>id</td>";
echo "<td>object name</td>";
echo "<td>date</td>";
echo "<td>status</td>";
echo "<td>comment</td>";
echo "</tr>\n";

$result = pg_query($dbconn, "SELECT id, object_name, date, status, maintenance.comment FROM objects INNER JOIN maintenance  USING (id) WHERE model=$model AND (status~'Broken' OR status~'Problem');");
while ($row=pg_fetch_assoc($result)) {
	echo "<tr class=\"tablerow\">";
	echo "<td><a href=\"object.php?object='{$row['id']}'\">{$row['id']}</a></td>";
	echo "<td>{$row['object_name']}</td>";
	echo "<td>{$row['date']}</td>";
	echo "<td>{$row['status']}</td>";
	echo "<td>{$row['comment']}</td>";
	echo "</tr>\n";
 }
echo "</table>\n";



echo "</div>";
page_foot();
?>
