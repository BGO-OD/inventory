<?php


include 'functions.php';


function check_present($value, $array) {
	for ($i=0;$i<sizeof($array);$i++) {
		if ($array[$i]['id']==$value) return true;
	}
	return false;
}
function get_item_for_location($value, $array) {
	for ($i=0;$i<sizeof($array);$i++) {
		if ($array[$i]['id']==$value) return $array[$i];
	}
}

$dbconn = pg_connect($dbstring);
if (!$dbconn) {
	  die('Could not connect: ' . pg_last_error());
}

$presentitems=array();
if (isset($_REQUEST['presentitems'])) {
	$presentitems=preg_split('/[\s,]+/',$_REQUEST['presentitems'],-1,PREG_SPLIT_NO_EMPTY);
}

$locationname="<= ";
$locationcomment="invalid location";

$location="";
if (isset($_REQUEST['location'])) {
	$location=$_REQUEST['location'];

	$roomdescription="";
	$result = pg_prepare($dbconn, "", 'SELECT object_name, comment FROM objects WHERE id=$1');
	$result = pg_execute($dbconn, "", array($location));
	if (pg_num_rows($result)>0) {
		$row=pg_fetch_assoc($result);
		$locationname=$row['object_name'];
		$locationcomment=$row['comment'];
	}
	$items=array();
	$result = pg_prepare($dbconn, "", 'SELECT
	                                     objects.id,
	                                     objects.location,
	                                     models.manufacturer,
	                                     models.name,
	                                     models.type
	                                   FROM
	                                     objects,
	                                     models
	                                   WHERE
	                                     models.model = objects.model AND
	                                     id IN ( 
	                                       WITH RECURSIVE csl(id) AS (
	                                         SELECT o.id FROM objects o WHERE o.location=$1
	                                         UNION ALL
	                                         SELECT o.id FROM csl sl, objects o WHERE sl.id = o.location
	                                       )
	                                       SELECT id FROM csl
	                                     )
	                                   ORDER BY objects.id;');
	$result = pg_execute($dbconn, "", array($location));
	if (pg_num_rows($result)>0) {
		while ($row=pg_fetch_assoc($result)) {
			$items[] = $row;
		}
	}

	$missingitems=array();
	$missingitemsinsub=array();
	$supernumeraryitems=array();
	for ($i=0;$i<sizeof($presentitems);$i++) {
		if (!check_present($presentitems[$i],$items)) {
			$supernumeraryitems[]=$presentitems[$i];
		}
	}
	for ($i=0;$i<sizeof($items);$i++) {
		if (!in_array($items[$i]['id'],$presentitems) && $items[$i]['location']==$location) {
			$missingitems[]=$items[$i]['id'];
		}
	}
	for ($i=0;$i<sizeof($items);$i++) {
		if (!in_array($items[$i]['id'],$presentitems) && $items[$i]['location']!=$location) {
			$missingitemsinsub[]=$items[$i]['id'];
		}
	}

	$result = pg_prepare($dbconn, "", 'SELECT
	                                     objects.id,
	                                     objects.location,
	                                     models.manufacturer,
	                                     models.name,
	                                     models.type
	                                   FROM
	                                     objects,
	                                     models
	                                   WHERE
	                                     models.model = objects.model AND
	                                     id IN ($1)
	                                   ORDER BY objects.id;');
	$itemsArray = array(join(',',$supernumeraryitems));
	if (sizeof($itemsArray) == 1) {
			$itemsArray=array("0");
	}
	$result = pg_execute($dbconn, "", $itemsArray);
	if (pg_num_rows($result)>0) {
		while ($row=pg_fetch_assoc($result)) {
			$items[] = $row;
		}
	}
}

if (isset($_REQUEST['json'])) {
	$json['items']=$items;
	$json['location']=$location;
	$json['presentitems']=$presentitems;
	$json['locationname']=$locationname;
	$json['locationcomment']=$locationcomment;
	$json['missingitems']=$missingitems;
	$json['missingitemsinsub']=$missingitemsinsub;
	$json['supernumeraryitems']=$supernumeraryitems;
	echo json_encode($json);
	exit;
}

page_head("$PROJECT_NAME","$PROJECT_NAME: Location check ".$location);
?>
<script>
	function get_item_for_location(value, arr) {
		for (var i=0;i<arr.length;i++) {
			if (arr[i].id==value) return arr[i];
		}
		return value;
	}

	function refreshdata() {
		var xmlhttp;
		var location = document.getElementById('locationfield').value;
		var presentitems = document.getElementById('presentitemsfield').value.replace(/(\r\n|\n|\r)/gm,",");;
		if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
			xmlhttp=new XMLHttpRequest();
		}
		else {// code for IE6, IE5
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				document.getElementById("debug").innerHTML="Location:"+location+"<br>Presentitems:"+presentitems+"<br>"+xmlhttp.responseText;
				var myArr = JSON.parse(xmlhttp.responseText);
				var text = "";
				for(var i = 0; i < myArr.missingitems.length; i++) {
					var item = get_item_for_location(myArr.missingitems[i], myArr.items);
					text = text+'<a href="object.php?object='+item.id+'">'+item.id+' ('+item.type+' '+item.manufacturer+' '+item.name+')</a><br />';
				}
				document.getElementById("missingitems").innerHTML=text;
				text = "";
				for(var i = 0; i < myArr.missingitemsinsub.length; i++) {
					var item = get_item_for_location(myArr.missingitemsinsub[i], myArr.items);
					text = text+'<a href="object.php?object='+item.id+'">'+item.id+' ('+item.type+' '+item.manufacturer+' '+item.name+')</a><br />';
				}
				document.getElementById("missingitemsinsub").innerHTML=text;
				text = "";
				for(var i = 0; i < myArr.supernumeraryitems.length; i++) {
					var item = get_item_for_location(myArr.supernumeraryitems[i], myArr.items);
					text = text+'<a href="object.php?object='+item.id+'">'+item.id+' ('+item.type+' '+item.manufacturer+' '+item.name+')</a><br />';
				}
				document.getElementById("supernumeraryitems").innerHTML=text;
				document.getElementById("locationdescription").innerHTML=myArr.locationname+" ("+myArr.locationcomment+")";
			}
		}
		xmlhttp.open("GET","locationcheck.php?json=true&location="+location+"&presentitems="+presentitems,true);
		xmlhttp.send();
	}
</script>
<div id=content>
	<h1>Location Check</h1>
	<form name="locationcheckform" method="get" action="locationcheck.php">
		<table>
			<tr>
				<th>Location:</th>
				<th><input id="locationfield" type="number" name="location" value="<?php echo $location;?>" onchange="refreshdata()"/></th>
				<th id="locationdescription"><?php echo $locationname." (".$locationcomment.")";?></th>
			</tr>
			<tr>
				<td style="vertical-align:top;">
					<b>Present items:</b>
					<br />
					<textarea id="presentitemsfield" name="presentitems" rows="15" oninput="refreshdata()"><?php echo implode("\n",$presentitems);?></textarea>
				</td>
				<td style="vertical-align:top;">
					<b>Missing items in location:</b>
					<br />
					<div id="missingitems"></div>
					<b>Missing items in sub locations:</b>
					<br />
					<div id="missingitemsinsub"></div>
				</td>
				<td style="vertical-align:top;">
					<b>Supernumerary items:</b>
					<br />
					<div id="supernumeraryitems"></div>
				</td>
			</tr>
			<tr>
				<td colspan="3"><input type="submit" value="Check"></td>
			</tr>
		</table>
		<div id="debug" style="display:none;"></div>
	</form>
</div>
<script>refreshdata();</script>
<?php
page_foot();
?>
