<?php

include 'functions.php';
include 'variables.php';

function dateFromPostgres($date) {
	return DateTime::createFromFormat('Y-m-d G:i:s', $date)->format('d.m.Y');
}

if (isset($_GET['order']) && is_numeric($_GET['order'])) {
	$order=$_GET['order'];
} else {
	$order = "0";
}

$neworder = false;

page_head("Order","B1 inventory: Order $order");
$dbconn = pg_connect($dbstring);
if (!$dbconn) {
	die('Could not connect: ' . pg_last_error());
};

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	switch ($_POST['submit']) {
	case 'update name':
	case 'update comment':
	case 'update company':
	case 'update orderdate':
	case 'update invoicedate':
	case 'update account':
	case 'update currency':
	case 'update state':
		$submitparts=explode(" ",$_POST['submit']);
		$field=$submitparts[1];
		if (empty($_POST[$field])) $_POST[$field] = 'NULL';
		$query="UPDATE orders SET $field='{$_POST[$field]}' WHERE number=$order;";
		$result=pg_query($dbconn,$query);
		break;
	case 'update netto':
	case 'update brutto':
	case 'update amount':
	case 'update besteller':
	case 'update signed_by':
		$submitparts=explode(" ",$_POST['submit']);
		$field=$submitparts[1];
		if (empty($_POST[$field])) $_POST[$field] = 'NULL';
		$query="UPDATE orders SET $field={$_POST[$field]} WHERE number=$order;";
		$result=pg_query($dbconn,$query);
		break;
	case 'add ordercomment':
		$result=pg_query($dbconn,"INSERT INTO ordercomments (comment,date,number) VALUES ('". pg_escape_string($dbconn,$_POST['ordercomment'])."', now(), $order);");
		break;
	case 'update order_weblink':
		$result=pg_query($dbconn,"UPDATE order_weblinks SET comment='". pg_escape_string($dbconn,$_POST['comment'])."' WHERE orderlinkid=".pg_escape_string($_POST['linkid']).";");
		break;
	case 'add order_file':
		if($_FILES['userfile']['error'] == UPLOAD_ERR_OK) {
			pg_query($dbconn, "begin");
			$oid = pg_lo_import($dbconn,$_FILES['userfile']['tmp_name']);
			pg_query($dbconn,"INSERT INTO files (file_name,mimetype,file,size) VALUES ('{$_FILES['userfile']['name']}', '{$_FILES['userfile']['type']}', $oid ,'{$_FILES['userfile']['size']}' );");
			$result=pg_query($dbconn,"INSERT INTO order_weblinks (number,link,comment) VALUES ($order,$oid,'".pg_escape_string($dbconn,$_POST['comment'])."'); ");
			pg_query($dbconn, "commit");
		} else {
			echo "<h2>Error uploading file. Error Code:" . $_FILES['userfile']['error'] ."</h2>";
		}
		break;
	case 'create order':
		$query="INSERT INTO orders (name, ordernumber, company, orderdate, invoicedate, account, netto, brutto, currency, amount, besteller, signed_by) VALUES (";
		$query.="'". pg_escape_string($dbconn,$_POST['name']) . "', ";
		$query.="'". pg_escape_string($dbconn,$_POST['ordernumber']) . "', ";
		$query.="'". pg_escape_string($dbconn,$_POST['company']) . "', ";
		if (isset($_POST['orderdate'])   && !empty($_POST['orderdate'])) {
			$orderdatets = strtotime($_POST['orderdate']);
			if ($orderdatets === false || $orderdatets == -1) {
				echo "<h2>Error: unrecognized date format for order date</h2>";
				$query.="NULL, ";
			} else {
				$query.="'". pg_escape_string($dbconn,date("Y-m-d",$orderdatets)) . "', ";
			}
		} else {
			$query.="NULL, ";
		}
		if (isset($_POST['invoicedate']) && !empty($_POST['invoicedate'])) {
			$invoicedatets = strtotime($_POST['invoicedate']);
			if ($invoicedatets === false || $invoicedatets == -1) {
				echo "<h2>Error: unrecognized date format for invoice date</h2>";
				$query.="NULL, ";
			} else {
				$query.="'". pg_escape_string($dbconn,date("Y-m-d",$invoicedatets)) . "', ";
			}
		} else {
			$query.="NULL, ";
		}
		$query.="'". pg_escape_string($dbconn,$_POST['account']) . "', ";
		if (isset($_POST['netto'])  && !empty($_POST['netto']))  $query.=pg_escape_string($dbconn,$_POST['netto']) . ", "; else $query.="NULL, ";
		if (isset($_POST['brutto']) && !empty($_POST['brutto'])) $query.=pg_escape_string($dbconn,$_POST['brutto']) . ", "; else $query.="NULL, ";
		$query.="'". pg_escape_string($dbconn,$_POST['currency']) . "', ";
		if (isset($_POST['amount']) && !empty($_POST['amount'])) $query.="'". pg_escape_string($dbconn,$_POST['amount']) . "', "; else $query.="NULL, ";
		if (isset($_POST['besteller']) && is_numeric($_POST['besteller'])) $query.="". pg_escape_string($dbconn,$_POST['besteller']) . ", "; else $query.="NULL, ";
		if (isset($_POST['signed_by']) && is_numeric($_POST['signed_by'])) $query.="". pg_escape_string($dbconn,$_POST['signed_by']) . ")"; else $query.="NULL)";
		$query.=" RETURNING number;";
		$result=pg_query($dbconn,$query);
		$row=pg_fetch_assoc($result);
		$order=$row['number'];
		break;
	}
}

echo "<datalist id=\"companies\">";
$result = pg_query($dbconn,"SELECT company FROM orders GROUP BY company ORDER BY company;");
while ($row=pg_fetch_assoc($result)) {
	echo "<option value=\"{$row['company']}\">";
}
echo "</datalist>\n";

echo "<datalist id=\"accounts\">";
$result = pg_query($dbconn,"SELECT account FROM orders GROUP BY account ORDER BY account;");
while ($row=pg_fetch_assoc($result)) {
	echo "<option value=\"{$row['account']}\">";
}
echo "</datalist>\n";

echo "<datalist id=\"currencies\">";
$result = pg_query($dbconn,"SELECT currency FROM orders GROUP BY currency ORDER BY currency;");
while ($row=pg_fetch_assoc($result)) {
	echo "<option value=\"{$row['currency']}\">";
}
echo "</datalist>\n";

$result = pg_query($dbconn,"SELECT *, (SELECT count(id) FROM objects WHERE order_number LIKE ('%05/' || substring(concat('0',number) from 3) || '%')) AS inventorycounts, (SELECT name FROM users WHERE userid=besteller) as bestellername, (SELECT name FROM users WHERE userid=signed_by) AS signature_name FROM orders WHERE number = $order;");
if ($row = pg_fetch_assoc($result)) {
	$id = $row['number'];
	$number = $row['ordernumber'];
	$name = $row['name'];
	$company = $row['company'];
	$orderdate = $row['orderdate'];
	$invoicedate = $row['invoicedate'];
	$netto = $row['netto'];
	$brutto = $row['brutto'];
	$amount = $row['amount'];
	$currency = $row['currency'];
	$account = $row['account'];
	$bestellername = $row['bestellername'];
	$signature_name = $row['signature_name'];
	$state = $row['state'];
	$inventorycounts = $row['inventorycounts'];
} else {
	$neworder = true;
	$id = $order;
	$number = date('Y').".31.";
	$name = "";
	$company = "";
	$orderdate = "";
	$invoicedate = "";
	$netto = "";
	$brutto = "";
	$amount = "";
	$currency = "EUR";
	$account = "ELSA";
	$bestellername = "";
	$signature_name = "";
	$state = "";
	$inventorycounts = 0;
}

if (!$neworder) echo "<div id=content><h1>Order $number</h1>\n";
else echo "<div id=content><h1>New Order</h1>\n";

echo '<form action="order.php?order='.$order.'" method="POST">'."\n";
echo "<table class=\"rundbtable\"></tr>\n";

if ($neworder) {
	echo "<tr><td>Order Number</td>";
	echo "<td><input type=\"text\" name=\"ordernumber\" size=\"20\" value=\"$number\"></td>";
	echo "</tr>\n";
}

echo "<tr><td>Summary</td>";
echo "<td><input type=\"text\" name=\"name\" size=\"20\" value=\"$name\"></td>";
if (!$neworder) echo "<td><button name=\"submit\" type=\"submit\" value=\"update name\" >Update</button></td>";
echo "</tr>\n";

echo "<tr><td>Company     </td>";
echo "<td><input list=\"companies\" type=\"text\" name=\"company\" size=\"20\" value=\"$company\"></td>";
if (!$neworder) echo "<td><button name=\"submit\" type=\"submit\" value=\"update company\" >Update</button></td>";
echo "</tr>\n";

echo "<tr><td>Number of inventorized objects</td>";
if ($inventorycounts > 0) {
	echo "<td><a href=\"objects.php?condition=order_number LIKE '%25".$number."%25'\">$inventorycounts</a></td>";
} else {
	echo "<td>$inventorycounts</td>";
}
if (!$neworder) echo "<td></td>";
echo "</tr>\n";

echo "<tr><td>Order date   </td>";
echo "<td><input type=\"date\" name=\"orderdate\" size=\"20\" value=\"$orderdate\"></td>";
if (!$neworder) echo "<td><button name=\"submit\" type=\"submit\" value=\"update orderdate\" >Update</button></td>";
echo "</tr>\n";

echo "<tr><td>Invoice date </td>";
echo "<td><input type=\"date\" name=\"invoicedate\" size=\"20\" value=\"$invoicedate\"></td>";
if (!$neworder) echo "<td><button name=\"submit\" type=\"submit\" value=\"update invoicedate\" >Update</button></td>";
echo "</tr>\n";

echo "<tr><td>State        </td>";
echo "<td><input type=\"text\" name=\"state\" size=\"20\" value=\"$state\"></td>";
if (!$neworder) echo "<td><button name=\"submit\" type=\"submit\" value=\"update state\" >Update</button></td>";
echo "</tr>\n";

echo "<tr><td>Account      </td>";
echo "<td><input type=\"text\" list=\"accounts\" name=\"account\" size=\"20\" value=\"$account\"></td>";
if (!$neworder) echo "<td><button name=\"submit\" type=\"submit\" value=\"update account\" >Update</button></td>";
echo "</tr>\n";

echo "<tr><td>Currency     </td>";
echo "<td><input type=\"text\" list=\"currencies\" name=\"currency\" size=\"20\" value=\"$currency\"></td>";
if (!$neworder) echo "<td><button name=\"submit\" type=\"submit\" value=\"update currency\" >Update</button></td>";
echo "</tr>\n";

if ($neworder) $currency = '';

echo "<tr><td>Netto        </td>";
echo "<td><input type=\"number\" min=\"0.00\" step=\"0.01\" name=\"netto\" size=\"20\" value=\"$netto\">$currency</td>";
if (!$neworder) echo "<td><button name=\"submit\" type=\"submit\" value=\"update netto\" >Update</button></td>";
echo "</tr>\n";

echo "<tr><td>Brutto       </td>";
echo "<td><input type=\"number\" min=\"0.00\" step=\"0.01\" name=\"brutto\" size=\"20\" value=\"$brutto\">$currency</td>";
if (!$neworder) echo "<td><button name=\"submit\" type=\"submit\" value=\"update brutto\" >Update</button></td>";
echo "</tr>\n";

echo "<tr><td>Really Paid  </td>";
echo "<td><input type=\"number\" min=\"0.00\" step=\"0.01\" name=\"amount\" size=\"20\" value=\"$amount\">EUR</td>";
if (!$neworder) echo "<td><button name=\"submit\" type=\"submit\" value=\"update amount\" >Update</button></td>";
echo "</tr>\n";

echo "<tr><td>Ordered by   </td>";
echo "<td><SELECT name=\"besteller\">";
echo "<OPTION value=\"-\">&nbsp;</OPTION>";
$result=pg_query($dbconn,"select userid, name, (select count(*) from orders where besteller=userid) as howoften from users order by howoften desc,  split_part(name,' ',2);");
while ($row=pg_fetch_assoc($result)) {
	if ($row['name'] == $bestellername) $sel = "selected";
	else $sel = "";
	echo "<OPTION ".$sel." value=\"".$row['userid']."\">" . $row['name'] . "</OPTION>";
}
echo "</SELECT></td>";
if (!$neworder) echo "<td><button name=\"submit\" type=\"submit\" value=\"update besteller\" >Update</button></td>";
echo "</tr>\n";

echo "<tr><td>Signed by    </td><td><SELECT name=\"signed_by\">";
echo "<OPTION value=\"-\">&nbsp;</OPTION>";
$result=pg_query($dbconn,"select userid, name, (select count(*) from orders where signed_by=userid) as howoften from users order by howoften desc,  split_part(name,' ',2);");
while ($row=pg_fetch_assoc($result)) {
	if ($row['name'] == $signature_name) $sel = "selected";
	else $sel = "";
	echo "<OPTION ".$sel." value=\"".$row['userid']."\">" . $row['name'] . "</OPTION>";
}
echo "</SELECT></td>";
if (!$neworder) echo "<td><button name=\"submit\" type=\"submit\" value=\"update signed_by\" >Update</button></td>";
echo "</tr>\n";

if ($neworder) echo "<tr><td colspan=\"2\"><button name=\"submit\" type=\"submit\" value=\"create order\" >Save</button></td></tr>\n";
else {
	echo "</table>\n";
	echo "<h2>Comments</h2>\n";
	echo "<table class=\"rundbtable\">\n";
	echo "<tr class=\"rundbhead\">";
	echo "<td>date</td>";
	echo "<td colspan=\"2\">comment</td>";
	echo "</tr>\n";
}
if (!$neworder) {
	$result = pg_query($dbconn,"SELECT * FROM ordercomments WHERE number = $order ORDER BY date DESC;");
	while ($row=pg_fetch_assoc($result)) {
		echo "<tr><td>${row['date']}</td><td colspan=\"2\">${row['comment']}</td>";
		if (!$neworder) echo "<td></td>";
		echo "</tr>\n";
	}
	echo "<tr><td colspan=\"2\"><input type=\"text\" name=\"ordercomment\" size=\"50\"></td>";
	echo "<td><button name=\"submit\" type=\"submit\" value=\"add ordercomment\" >Add Comment</button></td>";
	echo "</tr>\n";
}
echo "</table>\n";
echo "</form>\n";

if (!$neworder) {
	echo "<h2>Attachments</h2>\n";
	$result = pg_query($dbconn,"SELECT order_weblinks.link AS link, order_weblinks.comment AS comment, order_weblinks.orderlinkid AS orderlinkid, files.file_name AS filename FROM order_weblinks, files WHERE files.file = order_weblinks.link AND number=$order ORDER BY orderlinkid;");
	echo "<table class=\"rundbtable\">\n";
	echo "<tr class=\"rundbhead\">";
	echo "<td>link</td>";
	echo "<td>comment</td>";
	echo "<td></td>";
	echo "</tr>\n";
	while ($row=pg_fetch_assoc($result)) {
		echo "<tr class=\"rundbrun\">";
		echo "<form action=\"order.php?order=$order\" method=\"POST\">\n";
		echo "<td><a href=\"file.php?oid={$row['link']}\">{$row['filename']}</a></td>";
		echo "<td><input type=\"text\" name=\"comment\" size=20 value=\"{$row['comment']}\"></td>";
		echo "<td><input type=\"hidden\" name=\"linkid\" value=\"{$row['orderlinkid']}\"><button name=\"submit\" type=\"submit\" value=\"update order_weblink\" >Update</button></td>\n";
		echo "</form>\n";
		echo "</tr>\n";
	}
	echo "<tr class=\"rundbrun\">";
	echo "<form enctype=\"multipart/form-data\" action=\"order.php?order=$order\" method=\"POST\">\n";
	echo "    <!-- MAX_FILE_SIZE must precede the file input field -->\n";
	echo "    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"30000000\" />\n";
	echo "    <!-- Name of input element determines name in $_FILES array -->\n";
	echo "<td>file: <input name=\"userfile\" type=\"file\" /></td>\n";
	echo "<td><input type=\"text\" name=\"comment\" size=20 value=\"\"></td>";
	echo "<td><button name=\"submit\" type=\"submit\" value=\"add order_file\" >Add File</button></td>\n";
	echo "</form>\n";
	echo "</tr>\n";
	echo "</table>\n";
}

echo "</div>";

page_foot();
?>
