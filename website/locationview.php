<?php


include 'functions.php';
include 'variables.php';

$dbconn = pg_connect($dbstring);
if (!$dbconn) {
	  die('Could not connect: ' . pg_last_error());
}

page_head("B1 inventory","B1 inventory: Location view");
?>
<div id=content>
	<h2>Location Overview</h2>
	<canvas id="locationCanvas" width="1021" height="831">
	</canvas>
	<div id="link" style="display:none;"></div>
	<script>
		var Rooms = new Array();
		var hoverLink = "";
		var linkdiv = document.getElementById("link");
		var c = document.getElementById("locationCanvas");
		var ctx = c.getContext("2d");
		ctx.translate(0.5, 30.5);
		ctx.strokeStyle="#000000";
		ctx.fillStyle="#EEEEEE";
		ctx.textBaseline="top";
		ctx.globalAlpha=0.9;

		ctx.font="15px Verdana";
		ctx.strokeText("Wegeler Str. 10",0,-25);
		ctx.strokeText("Nussallee 12",720,-25);
		ctx.strokeText("Experimental Hall",0,275);
		ctx.font="10px Verdana";

		function getNumericStyleProperty(style, prop){
			return parseInt(style.getPropertyValue(prop),10) ;
		}

		function element_position(e) {
			var x = 0, y = 0;
			var inner = true ;
			do {
				x += e.offsetLeft;
				y += e.offsetTop;
				var style = getComputedStyle(e,null) ;
				var borderTop = getNumericStyleProperty(style,"border-top-width") ;
				var borderLeft = getNumericStyleProperty(style,"border-left-width") ;
				y += borderTop ;
				x += borderLeft ;
				if (inner){
				var paddingTop = getNumericStyleProperty(style,"padding-top") ;
				var paddingLeft = getNumericStyleProperty(style,"padding-left") ;
				y += paddingTop ;
				x += paddingLeft ;
				}
				inner = false ;
			} while (e = e.offsetParent);
			return { x: x, y: y };
		}

		function drawStairs(x,y,width,height,vertical) {
			ctx.strokeRect(x,y,width,height);
			ctx.beginPath();
			if (vertical) {
				ctx.moveTo(x,y+10);
				for (i = 10; i < height; i+=10) {
					var xx;
					if (i%20 == 0) xx = x;
					else xx =x+width;
					ctx.lineTo(xx,y+i);
					ctx.lineTo(xx,y+i+10);
				}
			} else {
				ctx.moveTo(x+10,y);
				for (i = 10; i < width; i+=10) {
					var yy;
					if (i%20 == 0) yy = y;
					else yy =y+height;
					ctx.lineTo(x+i,yy);
					ctx.lineTo(x+i+10,yy);
				}
			}
			ctx.stroke();
		}

		function drawRoom(x,y,width,height,number,id,basement,comment) {
			ctx.strokeRect(x,y,width,height);
			if (basement==true) ctx.fillRect(x,y,width,height);
			ctx.strokeText(number,x+5,y+5,width);
			Rooms.push(x + ";" + y + ";" + width + ";" + height + ";" + id);
		}

		function on_mousemove (ev) {
			var x, y;
			var p = element_position(c);
			x = ev.pageX - p.x;
			y = ev.pageY - p.y - 30;

			// Link hover
			for (var i = Rooms.length - 1; i >= 0; i--) {
				var params = new Array();

				// Get link params back from array
				params = Rooms[i].split(";");

				var linkX = parseInt(params[0]),
					linkY = parseInt(params[1]),
					linkWidth = parseInt(params[2]),
					linkHeight = parseInt(params[3]),
					linkHref = params[4];

				// Check if cursor is in the link area
				if (x >= linkX && x <= (linkX + linkWidth) && y >= linkY && y <= (linkY + linkHeight)){
					document.body.style.cursor = "pointer";
					hoverLink = linkHref;
					break;
				}
				else {
					document.body.style.cursor = "";
					hoverLink = "";
				}
			}
			linkdiv.innerHTML=hoverLink+" X: "+x+" Y: "+y;
		}

		// Link click
		function on_click(e) {
			if (hoverLink){
// 				window.open(hoverLink); // Use this to open in new tab
				window.location = hoverLink; // Use this to open in current window
			}
		}

		// Offices
		ctx.beginPath();
		ctx.moveTo(0,100);
		ctx.lineTo(1020,100);
		ctx.stroke();

		ctx.beginPath();
		ctx.moveTo(0,150);
		ctx.lineTo(720,150);
		ctx.stroke();

		ctx.beginPath();
		ctx.moveTo(720,100);
		ctx.lineTo(720,150);
		ctx.stroke();

		drawStairs(0,0,20,100, true);
		drawStairs(520,0,50,100, true);

		drawRoom(20,0,100,100,"W 0.014","object.php?object=1871",false,"");
		drawRoom(120,0,100,100,"W 0.016","object.php?object=1875",false,"");
		drawRoom(220,0,100,100,"W 0.017","object.php?object=1881",false,"");
		drawRoom(320,0,100,100,"W 0.019","object.php?object=1876",false,"");
		drawRoom(420,0,100,100,"W 0.021","object.php?object=1877",false,"");
		drawRoom(600,0,100,100,"W 1.019","object.php?object=2286",true,"");
		drawRoom(720,0,100,100,"E53/0.057","object.php?object=1865",false,"");
		drawRoom(820,0,100,100,"E52/0.056","object.php?object=1864",false,"");
		drawRoom(920,0,100,100,"E51/0.055","object.php?object=1866",false,"");
		drawRoom(220,150,100,100,"W 0.018","object.php?object=1863",false,"");
		drawRoom(420,150,100,100,"W 0.022","object.php?object=1897",false,"");
		drawRoom(520,150,100,100,"W 058","object.php?object=1861",true,"");
		drawRoom(620,150,100,100,"W 060","object.php?object=1862",true,"");
		drawRoom(720,100,300,150,"Experimental Hall","object.php?object=1874",true,"");

		// Hall
		drawStairs(100,600,50,100, true);
		drawStairs(100,750,50,50, true);
		drawStairs(200,750,100,50,false);
		drawStairs(200,350,150,50,false);
		drawStairs(700,300,150,50,false);
		drawStairs(600,600,50,50,false);

		ctx.lineWidth = 10;

		ctx.beginPath();
		ctx.moveTo(950,650);
		ctx.lineTo(950,300);
		ctx.lineTo(400,300);
		ctx.lineTo(400,350);
		ctx.lineTo(150,350);
		ctx.lineTo(150,700);
		ctx.lineTo(250,700);
		ctx.stroke();

		ctx.beginPath();
		ctx.moveTo(200,650);
		ctx.lineTo(900,650);
		ctx.lineTo(900,600);
		ctx.stroke();

		ctx.beginPath();
		ctx.moveTo(100,705);
		ctx.lineTo(100,295);
		ctx.stroke();

		ctx.lineWidth = 1;
		drawRoom(0,750,100,50,"Freimessecke","object.php?object=2562",true,"");
		drawRoom(550,350,50,50,"Rack 9","object.php?object=104",true,"");
		drawRoom(250,520,50,50,"Rack 11","object.php?object=105",true,"");
		drawRoom(750,655,50,50,"Rack 8","object.php?object=96",true,"");
		drawRoom(850,400,95,100,"GIM Compartment","object.php?object=2586",true,"");
		drawRoom(300,655,450,50,"Beside Area","object.php?object=1873",true,"");
		drawRoom(250,595,50,50,"Table","object.php?object=2555",true,"");
		drawRoom(300,615,100,30,"Cabinet","object.php?object=1868",true,"");

		ctx.fillStyle="#FF0000";
		ctx.fillRect(600,375,100,200);
		ctx.fillRect(250,440,80,80);
		ctx.fillStyle="#FFFF00";
		ctx.fillRect(155,465,30,30);
		ctx.fillStyle="#00FF00";
		ctx.fillRect(375,440,50,80);
		ctx.fillStyle="#000000";
		ctx.save();
		ctx.scale(1.5, 1);
		ctx.beginPath();
		ctx.arc(330, 475, 25, 0, 2 * Math.PI, false);
		ctx.restore();
		ctx.fill();
		ctx.fillStyle="#EEEEEE";

		// Platform
		ctx.fillStyle="#FFFFFF";
		ctx.fillRect(20,330,210,370);
		ctx.strokeRect(20,330,210,370);
		ctx.fillStyle="#EEEEEE";
		ctx.strokeText("Platform",25,335);
		Rooms.push(20 + ";" + 330 + ";" + 210 + ";" + 65 + ";" + "object.php?object=1870");
		Rooms.push(20 + ";" + 395 + ";" + 130 + ";" + 200 + ";" + "object.php?object=1870");
		drawRoom(150,395,50,40,"Rack 7","object.php?object=103",true,"");
		drawRoom(150,435,50,40,"Rack 6","object.php?object=102",true,"");
		drawRoom(150,475,50,40,"Rack 5","object.php?object=101",true,"");
		drawRoom(150,515,50,40,"Rack 4","object.php?object=100",true,"");
		drawRoom(150,555,50,40,"Rack 3","object.php?object=99",true,"");
		drawRoom(150,595,50,40,"Rack 2","object.php?object=98",true,"");
		drawRoom(150,635,50,40,"Rack 1","object.php?object=97",true,"");

		drawRoom(50,505,50,40,"Delay Rack","object.php?object=2559",true,"");
		drawRoom(50,635,50,40,"Power Rack","",true,"");

		drawStairs(50,350,50,50, false);

		drawRoom(955,295,65,505,"Ramp","",true,"");

		c.addEventListener("mousemove", on_mousemove, false);
		c.addEventListener("click", on_click, false);
	</script>
	<h2>Other Locations</h2>
	<table class="tabletable">
		<tr class="tablehead">
			<td>Name</td>
			<td>Description</td>
			<td>Object id</td>
			<td># objects there</td>
		</tr>
 <?php
			$result = pg_query($dbconn,"SELECT id,object_name,comment,(select count(*) from (WITH RECURSIVE csl(id) AS (SELECT o.id FROM objects o WHERE o.location=objects.id UNION ALL SELECT o.id FROM csl sl, objects o WHERE sl.id = o.location)SELECT id FROM csl) as foo) as count FROM objects WHERE id in (2143,2355,2259,2236,1883,2726,1882,1872,2320,1988,1985) ORDER BY object_name, id;");
      while ($row = pg_fetch_assoc($result)) {
	      echo "<tr class=\"tablerow\">\n";
	      echo "<td>${row['object_name']}</td>";
	      echo "<td>${row['comment']}</td>";
	      echo "<td><a href=\"object.php?object=${row['id']}\">${row['id']}</a></td>";
	      echo "<td><a href=\"objects.php?condition=id in (WITH RECURSIVE csl(id) AS (SELECT o.id FROM objects o WHERE o.location=${row['id']} UNION ALL SELECT o.id FROM csl sl, objects o WHERE sl.id = o.location) SELECT id FROM csl)\">${row['count']}</a></td>";
	      echo "</tr>\n";
      }
?>
	</table>
</div>
<?php
page_foot();
?>
