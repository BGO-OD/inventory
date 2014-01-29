<?php
include '../common/page_functions.php';
include 'functions.php';
include 'variables.php';

function dateFromPostgres($date) {
	return DateTime::createFromFormat('Y-m-d G:i:s', $date)->format('d.m.Y');
}

if (isset($_GET['order'])) {
	$order=$_GET['order'];
 } else {
	die ("no order given");
 }


page_head("Order","B1 inventory: Order $order");
$dbconn = pg_connect($dbstring);
if (!$dbconn) {
	die('Could not connect: ' . pg_last_error());
 };

echo "<div id=content><h1>Order $order</h1>\n";

$result = pg_query($dbconn,"SELECT *, (SELECT count(id) FROM objects WHERE order_number LIKE ('%05/' || substring(concat('0',number) from 3) || '%')) AS inventorycounts FROM orders WHERE number = $order;");
if ($row = pg_fetch_assoc($result)) {
	echo "<table class=\"rundbtable\"></tr>\n";
	echo "<tr><td>Summary</td><td>${row['name']}</td></tr>\n";
	echo "<tr><td>Number of inventorized objects</td>";
	if ($row['inventorycounts'] > 0) {
		echo "<td><a href=\"objects.php?condition=order_number LIKE '%2505/".substr($row['number'],1)."%25'\">${row['inventorycounts']}</a></td></tr>\n";
	}	else {
		echo "<td>${row['inventorycounts']}</td></tr>\n";
	}
	echo "<tr><td>Order date</td><td>${row['orderdate']}</td></tr>\n";
	echo "<tr><td>Invoice date</td><td>${row['invoicedate']}</td></tr>\n";
	echo "<tr><td>State</td><td>${row['state']}</td></tr>\n";
	echo "<tr><td>Account</td><td>${row['account']}</td></tr>\n";
	echo "<tr><td>Netto</td><td>${row['netto']}${row['currency']}</td></tr>\n";
	echo "<tr><td>Brutto</td><td>${row['brutto']}${row['currency']}</td></tr>\n";
	echo "<tr><td>Really Paid</td><td>${row['amount']} EUR</td></tr>\n";
	echo "<tr><td>Ordered by</td><td>${row['besteller']}</td></tr>\n";
	echo "<tr><td>Signed by</td><td>${row['signature']}</td></tr>\n";

	$result = pg_query($dbconn,"SELECT * FROM ordercomments WHERE number = $order ORDER BY date DESC;");
	while ($row=pg_fetch_assoc($result)) {
		echo "<tr><td>${row['date']}</td><td>${row['comment']}</td></tr>\n";
	}
	echo "</table>\n";

 } else {
	echo "No order with this number found!\n";
 }
	echo "</div>";

	page_foot();
?>
