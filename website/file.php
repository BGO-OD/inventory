<?php


if(isset($_GET['oid'])) {
	$oid= $_GET['oid'];
	
} else {
	die('No file name given');
}
$dbconn = pg_connect($dbstring);
if (!$dbconn) {
	die('Could not connect: ' . pg_last_error());
};

$result = pg_query($dbconn, "SELECT file, mimetype, file_name, size FROM files WHERE file = '$oid' LIMIT 1;" );
if (!$result) {
	echo "An error occured: " .pg_last_error();
	exit;
}


if (pg_num_rows($result) > 0) {
	while ($row = pg_fetch_assoc($result)) {
		header("Content-Type: {$row['mimetype']}");
		header("Content-Disposition: inline; filename=\"${row['file_name']}\";");
    if ($row['size']!="") {
      header("Content-Length: ".$row['size']);
    }
		pg_query($dbconn, "begin");
		$handle = pg_lo_open($dbconn, $row['file'], "r");
		pg_lo_read_all($handle);
		pg_lo_close($handle);
		pg_query($dbconn, "commit");
	}
} else {
	echo "Could not find file with oid $oid in database" ;
}
?>
