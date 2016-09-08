<?php

include 'variables.php';

$enable_location_select=false;

function get_location($dbconn,$id,$with_links=TRUE) {
	$string="";
	$result=pg_query($dbconn,"SELECT location,location_description FROM objects WHERE id='$id';");
	if ($row=pg_fetch_assoc($result)) {
		$id=$row['location'];
		if($row['location_description'] != '') {
			$string="(${row['location_description']})";
		}
	} else {
		return "Object location not found!";
	}

	do {
		$result=pg_query($dbconn,"SELECT id,location,object_name,models.type, location_description FROM objects inner join models on models.model=objects.model WHERE id='$id';");
		if ($row=pg_fetch_assoc($result)) {
			if ($string!="") {
				$string="&raquo;".$string;
			}

			if ($with_links) {
				$string="</a>".$string;
			}
			$object_name=$row['object_name'];
			if($object_name == '') {
				$object_name = $row['type'].' '.$row['id'];
			}
			$string=$object_name.$string;
			if ($with_links) {
				$string="<a href=\"object.php?object=$id\">".$string;
			}
			if($row['location_description'] != '') {
				$string="(".$row['location_description'].")&raquo;".$string;
			}
			$id=$row['location'];
		} else {
			break;
		}
	} while ($id!=NULL);
	return $string;
	}

function navigation_bar() {
	echo "<DIV id=\"navigation\">\n";
	echo "<a class=\"navbutton\" href=\"models.php\">Models list</a>\n";
	echo "<a class=\"navbutton\" href=\"objects.php\">Objects list</a>\n";
	echo "<a class=\"navbutton\" href=\"objects.php?condition=true&limit=20\">new Objects list</a>\n";
	echo "<a class=\"navbutton\" href=\"objects.php?condition=maintenance_instructions='E-check'%20AND%20next_maintenance%20-%20interval%20'1%20year'%3Cnow()&order=next_maintenance\">E-check overdue</a>\n";
	echo "<a class=\"navbutton\" href=\"objects.php?condition=echeck_inventory_number%20is%20not%20null&order=echeck_inventory_number\">Objects with E-check id</a>\n";
	echo "<a class=\"navbutton\" href=\"objects.php?condition=echeck_inventory_number%20in%20(select%20echeck_inventory_number%20from%20(select%20echeck_inventory_number,count(*)%20as%20multiplicity%20from%20objects%20where%20echeck_inventory_number%20is%20not%20null%20group%20by%20echeck_inventory_number)%20as%20mult%20where%20multiplicity%20%3E%201)&order=echeck_inventory_number\">Objects with ambiguous E-check id</a>\n";
	echo "<a class=\"navbutton\" href=\"objects.php?condition=id%20in%20(select%20id%20from%20objects%20natural%20inner%20join%20(select%20model,serial%20from%20(select%20model,%20serial,%20%20count(*)%20%20as%20c%20from%20objects%20inner%20join%20models%20using%20(model)%20where%20serial%20!=%20''%20group%20by%20model,serial)%20as%20foo%20where%20c%3E1)%20as%20bar)&order=model\">Objects ambiguous serial</a>\n";
	echo "<a class=\"navbutton\" href=\"models.php?condition=maintenance_instructions!~'E-check'%20%20and%20type%20not%20in%20('Photomultiplier','Photomultiplier%20Base','DIN-Rail%20stuff','Board','Location','Detector','Hard%20Disk','NIM%20Module','VME%20Module','HV%20Module')\">Models with no E-check instruction</a>\n";
	echo "<a class=\"navbutton\" href=\"locationcheck.php\">Location check</a>\n";
	echo "<a class=\"navbutton\" href=\"users.php\">User list</a>\n";
	echo "<a class=\"navbutton\" href=\"owners.php\">Owner list</a>\n";
	echo "<a class=\"navbutton\" href=\"orders.php\">Order list</a>\n";
	echo "<form action=\"object.php\" method=\"get\">";
	echo "<div id=navsection>";
	echo "goto Object ";
	echo "<input type=\"text\" size=\"5\" name=\"object\"/>";
	echo "</div>\n";
	echo "</form>";
	echo "</DIV>\n";
}

function extra_header_content() {
	global $enable_location_select;
	if( $enable_location_select == TRUE ) {
	echo <<<EOT
		<script type="text/javascript">
		var xmlhttp;
		function reLoad() {
			var select = document.getElementById("initial_location_selector");
			select.form.reset();
		}
		window.onload = reLoad;
		function nextSelectLocationBox(caller,id)
		{
			var option=caller.value;
			var parent_span =caller.parentNode;
			if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp=new XMLHttpRequest();
			} else {// code for IE6, IE5
				xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			xmlhttp.open("GET","location_serv.php?location="+option+"&id="+id,false);
			xmlhttp.send(null);
			if(xmlhttp.responseText != "") {
				parent_span.innerHTML=xmlhttp.responseText;
			}
		}
		</script>
EOT;
	}
}

function select_location($dbconn,$id="",$location="") {
	echo "<span id=\"selectLocation_container\" class=\"select_location\">";
	location_serv($dbconn, $id,$location);
	echo "</span>\n";
}

function location_serv($dbconn, $id="",$location="") {
	if( $id=="") {
		$id="0";
	}

	if( $location=="") {
		$location="0";
	}

	$result = pg_query($dbconn, "SELECT location_description FROM objects WHERE id=$id;");
	if( $row=pg_fetch_assoc($result) ) {
		$location_description=$row['location_description'];
	}



	$location_list=array();

	while($location != "") {
		$result = pg_query($dbconn, "SELECT location FROM objects WHERE id=$location;");
		if(pg_num_rows($result) > 0) {
			$location_list[]=$location;
			$row=pg_fetch_assoc($result);
			$location=$row['location'];
		} else {
			break;
		}
	}

	$result = pg_query($dbconn, "SELECT id,object_name, sublocations FROM objects  inner join models on objects.model=models.model WHERE location is NULL ORDER BY object_name;");

	$name="";
	if(count($location_list) <= 1) {
		$name="location";
	}
	echo "<SELECT id=\"initial_location_selector\" name=\"$name\" onChange=\"javascript: nextSelectLocationBox(this,$id)\" >\n";
	echo "<OPTION value=0></OPTION>\n";
	while ($row=pg_fetch_assoc($result)) {
		if(count($location_list) >0 && $row['id'] == $location_list[count($location_list)-1]) {
			$selected="selected";
			$sublocations=$row['sublocations'];
		}else {
			$selected="";
		}
		echo "<OPTION value={$row['id']} $selected>{$row['object_name']}</OPTION>\n";
	}
	echo "</SELECT>";

	for($i=count($location_list)-1; $i>=0; $i--) {
		$result = pg_query($dbconn, "SELECT id, object_name, models.type, sublocations FROM objects inner join models on objects.model=models.model WHERE location = $location_list[$i] AND sublocations IS NOT NULL AND sublocations != ''  ORDER BY object_name;");
		if(pg_num_rows($result) > 0) {
			$name="";
			if($i ==1) {
				$name="location";
			}
			echo "&raquo;";
			echo "<SELECT  name=\"$name\" onChange=\"javascript: nextSelectLocationBox(this,$id)\" >\n";
			echo "<OPTION value=\"$location_list[$i]\"></OPTION>\n";
			while ($row=pg_fetch_assoc($result)) {
				
				if($i>0 && $row['id'] == $location_list[$i-1]) {
					$selected="selected";
					$sublocations=$row['sublocations'];
	// 				echo "<!--$sublocations-->";
				}else {
					$selected="";
				}
				if($id== $row['id']) {
					$selected ="disabled";
				}
				$object_name=$row['object_name'];
				if($object_name == '') {
					$object_name = $row['type'].' '.$row['id'];
				}
				echo "<OPTION value=\"{$row['id']}\" $selected>$object_name</OPTION>\n";
			}
			echo "</SELECT>";
		}
	}



	if($sublocations!='individual') {
		echo "<br> Location description: ";
		echo "<!--$sublocations-->";
		echo "<SELECT  name=\"location_description\">\n";

		$sublocs=explode(",",$sublocations);
		foreach ($sublocs as $subloc) {
			$parts=explode(" ",ltrim($subloc));
			if (strpos($parts[0],"-")===FALSE) {
				$name="";
				for ($j=0; $j<count($parts); $j++) {
					$name.=$parts[$j]." ";
				}
				if($name == $location_description) {
					$selected="selected";
				}else {
					$selected="";
				}
				echo "<OPTION value=\"$name\" $selected>$name</OPTION>\n";

			} else {
				$fromto=explode("-",$parts[0]);
				for ($i=$fromto[0]; $i<=$fromto[1]; $i++) {
					$name="";
					for ($j=1; $j<count($parts); $j++) {
						$name.=$parts[$j]." ";
					}
					$name.=$i;
					if($name == $location_description) {
						$selected="selected";
					}else {
						$selected="";
					}
					echo "<OPTION value=\"$name\" $selected>$name</OPTION>\n";
				}
			}
		}
		echo "</SELECT>";
	} else {
		echo "<br> Location description: <input type=\"text\" name=\"location_description\" size=40 value=\"$location_description\">";
	}

}

function select_user($dbconn,$olduser="",$inputname="userid") {
	$result = pg_query($dbconn, "SELECT userid,name FROM users ORDER BY split_part(name,' ',2);");
	
	echo "<SELECT name=\"$inputname\">\n";
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
		if ($oldowner==$row['ownerid']) {
			$sel="selected";
		} else {
			$sel="";
		}
		echo "<OPTION $sel value={$row['ownerid']}>{$row['owner_name']}</OPTION>\n";
	}
	echo "</SELECT>\n";
}



function page_head($title, $name, $refresh_interval=0) {
		global $PROJECT_NAME, $PROJECT_URL, $PROJECT_LOGO, $PROJECT_SUBTITLE;
		
    echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\"\n";
    echo "                 \"http://www.w3.org/TR/html4/strict.dtd\">\n";
    echo "<HTML>\n";
    echo "<HEAD>\n";
    echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"> \n";
		if ($refresh_interval > 0) {
				echo "<meta http-equiv=\"refresh\" content=\"".$refresh_interval."\">\n";
		}
    echo "<TITLE>$name</TITLE>\n";
    echo "<link rel=\"SHORTCUT ICON\" href=\"favicon.ico\">\n";
    echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"style.css\">\n";
    
    extra_header_content();
    
    echo "</HEAD>\n";
    echo "\n";
    echo "<BODY>\n";

    if (!isset($_GET['nonavi'])) {
				echo "<div id=header>\n";
				echo "  <div id=page-title>\n";
				echo "      <h1> <a href=\"./index.php\" title=\"$title\">$title</a></h1>\n";
				echo "  </div>\n";
				echo "  <div id=\"logo\">\n";
				echo "      <a href=\"$PROJECT_URL\" title=\"$PROJECT_NAME\"><img src=\"$PROJECT_LOGO\" alt=\"$PROJECT_NAME\" /></a>\n";
				echo "  </div>\n";
				echo "  <div id=\"slogan-floater\">\n";
				echo "      <h1 class='site-name'><a href=\"$PROJECT_URL\" title=\"$PROJECT_NAME\">$PROJECT_NAME</a></h1>\n";
				echo "      <div class='site-slogan'>$PROJECT_SUBTITLE</div>\n";
				echo "  </div>\n";
				echo "</div>\n";

				echo "<div id=main>";
				navigation_bar();
    } else {
				echo "<div id=main>";
		}
}



function page_foot() {
	  echo "</DIV>\n";
		echo "</DIV>\n";
		echo "</body>\n";
}


function show_table($dbconn,$selection,$table,$items) {
		$result = pg_query($dbconn,"SELECT $selection FROM $table;");
		while ($row=pg_fetch_assoc($result)) {
				echo "<tr class=\"tablerow\">";
				foreach ($items as $item) {
				}
				echo "</tr>\n";
		}
}
?>
