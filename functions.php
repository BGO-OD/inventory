<?php

function get_location($dbconn,$location,$with_links=TRUE) {
	$string="";
	do {
		$result=pg_query($dbconn,"SELECT parent_location,type,location_name FROM locations WHERE location='$location';");
		if ($row=pg_fetch_assoc($result)) {
			if ($string!="") {
				$string="&raquo;".$string;
			}
			if ($with_links) {
				$string="</a>".$string;
			}
			$string=$row['location_name'].$string;
			if ($with_links) {
				$string="<a href=\"location.php?location=$location\">".$string;
			}
			$location=$row['parent_location'];
		} else {
			break;
		}
	} while ($location!=NULL);
	return $string;
	}

function navigation_bar() {
	echo "<DIV id=\"navigation\">\n";
	echo "<a class=\"navbutton\" href=\"locations.php\">Locations list</a>\n";
	echo "<a class=\"navbutton\" href=\"models.php\">Models list</a>\n";
	echo "<a class=\"navbutton\" href=\"objects.php\">Objects list</a>\n";
	echo "<a class=\"navbutton\" href=\"users.php\">User list</a>\n";
	echo "<a class=\"navbutton\" href=\"owners.php\">Owner list</a>\n";
	echo "</DIV>\n";
}

function extra_header_content() {
	echo <<<EOT
		<script type="text/javascript">
		var selects=[];
		var xmlhttp;
		function nextSelectLocationBox(caller)
		{
			if(selects.length==0) {
				selects[0]=caller;
			}
			var parent_select =0;
			for (i=selects.length-1;i>=0;i--) {
				if(selects[i]==caller) {
					parent_select=i-1;
					break;
				}
				selects[i].parentNode.removeChild(selects[i]);
				selects.length--;
			}
			var option=caller.value;
			
			for (i=0; i<selects.length; i++) {
				selects[i].setAttribute("name","old");
			}
			
			if(option !=0) {
				caller.setAttribute("name","location");
				
				if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp=new XMLHttpRequest();
				} else {// code for IE6, IE5
					xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.open("GET","location_serv.php?location="+option,false);
				xmlhttp.send();
				if(xmlhttp.responseText != "") {
					var element = document.createElement("select");
					element.setAttribute("onChange","javascript: nextSelectLocationBox(this)");
					element.innerHTML=xmlhttp.responseText;
					selects[selects.length] = element;
					var form1 = document.getElementById("selectLocation");
					form1.appendChild(element);
				
				}
			} else {
				if(parent_select>=0) {
					selects[parent_select].setAttribute("name","location");
				} else {
					caller.setAttribute("name","location");
				}
			}
	
		}
		</script>
EOT;
}

function select_location($dbconn) {
	$result = pg_query($dbconn, "SELECT location,location_name,type FROM locations WHERE parent_location is NULL ORDER BY location_name;");
	
	echo "<SPAN id=\"selectLocation\">";
	echo "<SELECT name=\"location\" onChange=\"javascript: nextSelectLocationBox(this)\">\n";
	echo "<OPTION value=0></OPTION>\n";
	
	while ($row=pg_fetch_assoc($result)) {
		echo "<OPTION value={$row['location']}>{$row['type']} {$row['location_name']}</OPTION>\n";
	}
	echo "</SELECT></SPAN>";
}

function select_user($dbconn,$olduser="") {
	$result = pg_query($dbconn, "SELECT userid,name FROM users;");
	
	echo "<SELECT name=\"userid\">\n";
	if ($olduser=="") {
		$sel="selected";
	} else {
		$sel="";
	}
	echo "<OPTION $sel value='NULL'>no one</OPTION>\n";

	while ($row=pg_fetch_assoc($result)) {
		if ($olduser==$row['name']) {
			$sel="selected";
		} else {
			$sel="";
		}
		echo "<OPTION $sel value={$row['userid']}>{$row['name']}</OPTION>\n";
	}
	echo "</SELECT>\n";
}

function select_owner($dbconn,$oldowner="") {
	$result = pg_query($dbconn, "SELECT ownerid,owner_name FROM owners;");
	
	echo "<SELECT name=\"ownerid\">\n";
	if ($oldowner=="") {
		$sel="selected";
	} else {
		$sel="";
	}
	echo "<OPTION $sel value='NULL'>no one</OPTION>\n";

	while ($row=pg_fetch_assoc($result)) {
		if ($oldowner==$row['owner_name']) {
			$sel="selected";
		} else {
			$sel="";
		}
		echo "<OPTION $sel value={$row['ownerid']}>{$row['owner_name']}</OPTION>\n";
	}
	echo "</SELECT>\n";
}




?>
