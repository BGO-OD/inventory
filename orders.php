<?php
	include '../common/page_functions.php';
	include 'functions.php';
	include 'variables.php';

	function dateFromPostgres($date) {
		return DateTime::createFromFormat('Y-m-d G:i:s', $date)->format('d.m.Y');
	}

	page_head("Orders","B1 inventory: Orders");
	$dbconn = pg_connect($dbstring);
	if (!$dbconn) {
		die('Could not connect: ' . pg_last_error());
	};

	echo '<div id=content><h1>Orders</h1>';

	echo "<table class=\"rundbtable\">\n";
	
	echo "<tr class=\"rundbhead\">";
	echo "<td>Order Number</td>";
	echo "<td>Summary</td>";
	echo "<td>Order Date</td>";
	echo "<td>Invoice Date</td>";
	echo "<td>Price netto/brutto/paid (Currency)</td>";
	echo "<td>Orderer</td>";
	echo "<td>Comment</td>";
	echo "<td>Signature</td>";
	echo "</tr>\n";
	
	$result = pg_query($dbconn, "SELECT number, name, orderdate, invoicedate, netto, brutto, currency, amount, besteller, comment, signature, (SELECT count(id) FROM objects WHERE order_number LIKE ('%05/' || substring(concat('0',number) from 3) || '%')) AS inventorycounts FROM orders ORDER BY number DESC;");
	while ($row=pg_fetch_assoc($result)) {
		echo "<tr class=\"rundbrun\">";
		echo "<td>"; if ($row['inventorycounts'] > 0) echo "<a href=\"objects.php?condition=order_number LIKE '%2505/".substr($row['number'],1)."%25'\">05/".substr($row['number'],1)."</a>"; else echo "05/".substr($row['number'],1); echo "</td>";
		echo "<td>{$row['name']}</td>";
		echo "<td>"; if (!empty($row['orderdate'])) echo dateFromPostgres($row['orderdate']);   echo "</td>";
		echo "<td>"; if (!empty($row['invoicedate'])) echo dateFromPostgres($row['invoicedate']); echo "</td>";
		echo "<td>{$row['netto']}/{$row['brutto']}/{$row['amount']} ({$row['currency']})</td>";
		echo "<td>{$row['besteller']}</td>";
		echo "<td>{$row['comment']}</td>";
		echo "<td>{$row['signature']}</td>";
		echo "</tr>\n";
	}
	echo "</table>\n";

	echo "</div>";

	page_foot();
?>
