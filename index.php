<?php

include '../common/page_functions.php';
include 'functions.php';
include 'variables.php';



page_head("B1 inventory","B1 inventory");

echo '<div id=content><h1></h1>';

include '../common/svn_version.php';

echo "website svn revision $SVN_REVISION<br>\n";
echo "svn exceptions: <br>\n";
foreach ($SVN_EXCEPTIONS as $exception) {
	echo "$exception<br>\n";
} 


echo "</div>";
page_foot();
?>
