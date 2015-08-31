<!doctype html> 
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Rickshaw / D3 graphs </title>

		<!-- Bootstrap -->
		<!-- link href="css/bootstrap.min.css" rel="stylesheet" -->

		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		  <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
		<!-- dumb mistake: putting "/" at beginning of source files doesn't mean 'this directory' -->
		<link rel="stylesheet" href="rickshaw.min.css">
		<link type="text/css" rel="stylesheet" href="stocks.css">
		<script src="d3.min.js"></script> 
		<script src="d3.v2.js"></script>
		<script src="d3.layout.min.js"></script> 
		<script src="rickshaw.min.js"></script> 
		
<?php	
	//set up unique style tags/blocks 
	//for each graph in header
	$stockNames = array("TRIP","GOOGL","GOOG","TGT","AAPL","LNKD");//,"GS","ADM","XES","AMZN","CAT","CHK","CTXS","DE","DG","DIS","KS","DPZ","EBAY","ED","EEM","FB","FDX","FFIV","FOSFX","GE","GIS","GLW","HD","HTA","IBM","JMBA","KBH","LOW","MCD","MSFT","ORCL","PCAR","RVBD","SLB","TOL","VMW","VWIGX","WFM","ANF","AVP","BBY","C");
	
	foreach ($stockNames as $sym){
		$headStyle = "<style>"
		."			#".$sym."_chart_container {"
		."					width: 960px;"
		."					position: relative;"
		."					font-family: Arial, Helvetica, sans-serif;"
		."			}"
		."			#".$sym."_chart {"
		."					position: relative;"
		."					left: 40px;"
		."			}"
		."			#".$sym."_y_axis {"
		."					position: absolute;"
		."					top: 0;"
		."					bottom: 0;"
		."					width: 40px;"
		."			}"
		."			#".$sym."_legend {"
		."				text-align: center;"
		."			}"
		."		</style>";
		echo $headStyle;
	}
?>
	</head>
	<body>

		<h1>Stock Tracker -- Pseudo Account</h1>
		<p>
		  A 'view' of a pseudo-portfolio of stocks.
		</p>

		<!-- h2>June 22, 2014</h2 -->
		<p><img src="images/bullbear.gif" alt="bull and bear"></p>
		<p>
		  Here's a table presenting some stocks: 
		</p>

		<table>
		  <caption>
			Stocks being monitored
		  </caption>
		  <tr>
			<th colspan="2">Individual (Fidelity)</th>
			<th colspan="4">Most Recent</th>
			<th colspan="2">Change Since Close</th>
			<th colspan="3">Change Since Purchase</th>
			<th colspan="3">Cost Basis</th>
		  </tr>
		  <tr>
			<th>Symbol</th>
			<th>Name</th>
			<th>Quantity</th>
			<th>Price</th>
			<th>Change</th>
			<th>Value</th>
			<th>$</th>
			<th>%</th>
			<th>$</th>
			<th>%</th>
			<th>%peryear</th>
			<th>Per Share</th>
			<th>Total</th>
			<th>Date Acquired</th>
		  </tr>

		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="js/bootstrap.min.js"></script>	
		
	
<?php		

	require_once('Stock.php');
	//$colors = array("#0000FF", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33");
	$date = new DateTime(); // get today's date		
	$startDate = "2014-06-01";
	$endDate = $date->format('Y-m-d'); // today's date
		
	$amt = 1;
	$min = 9999;
	$stockObjects = array();
	foreach($stockNames as $symb){
	
		$theStockObj = new Stock();
		$theStockObj->setSymbol($symb);
		$theStockObj->setQuantity($amt);
		$theStockObj->setStartDate($startDate);
		$theStockObj->setEndDate($endDate);
		$theStockObj->setAccount("ScotTrade IRA");		
		$stockObjects[] = $theStockObj;
		$name = $theStockObj->getName();

		// set up div to hold each graph
		$stockDiv = ""
/*		."<div id=\"".$symb."_chart_container\">"
		."	<table>"
		."		<tr>"
		."			<td>".$name	."</td>"
		."		</tr>"
		."		<tr>"
		."			<td>"
		."				<div id=\"".$symb."_legend\"></div>"
		."			</td>"
		."			<td>"
		."			</td>"
		."			<td>"
		."				<div id=\"".$symb."_y_axis\"></div>"
		."				<div id=\"".$symb."_chart\"></div>"
		."			</td>"
		."		</tr>"
		."	</table>"
		."</div>";
	
*/ // "stockdiv" misnamed as I develop this table (replaced the chart div as I cobble this together)
		."<tr>"
		."	<td>".$symb."</td>"
		."	<td class=\"center\">".$name."</td>"
		."	<td class=\"center\">".$amt."</td>"             
		."	<td class=\"right\">current$</td>"
		."	<td class=\"right\">calcChng</td>"
		."	<td class=\"center\">calc$amt</td>"
		."	<td class=\"center\">dontneed</td>"
		."	<td class=\"center\">dontneed</td>" 
		."	<td class=\"center\">totChng$</td>"
		."	<td class=\"center\">totChng%</td>" 
		."	<td class=\"center\">YrlyChg%</td>" 
		."	<td class=\"center\">price$</td>"
		."	<td class=\"center\">calcTot$</td>"
		."	<td class=\"center\">dateAcq</td>"
		."</tr>";
		
		// add amount to database / need form to do so...
		//OY! need individual purchase amts!  e.g., goog: 1 on 3/2/14, 5 on 6/19/14
		// need to grab "current price" from db 
		// need to grab "yesterday's price" from db 
		// need to grab "price bought" from db 
		// need to grab "date acquired" from db 
		
		// this is where the foregoing block is 'echoed' to the html page
		echo $stockDiv;
		//echo "<br>".$stockDiv."<br><hr>";
		
	}	
	
/*		
	//	use Stock objects to build rickshaw graph
	foreach($stockObjects as $s){	
		$text = ""
		."<script>"
		."	var graph = new Rickshaw.Graph( {"
		."		element: document.getElementById(\"".$s->getSymbol()."_chart\"),"
		."		width: 400,"
		."		height: 100,"
		."		renderer: 'line',"
		."		stroke: true,"	
		."		series: [ ";
		$color = "#6699FF"; //array_pop($colors);
		$text .= "{	data: [  ".$s->getData()." ], "
		."		scale: d3.scale.linear().domain(["
		.		$s->getMin().", ".$s->getMax()."]).nice(),"		
		."		color: '$color', name: '".$s->getSymbol()."'}";
		$text .= "] } );"	
		."	var xAxis = new Rickshaw.Graph.Axis.Time({"
		."		graph: graph,"
		."		timeFixture: new Rickshaw.Fixtures.Time(),"
		."	});"
		."	var yAxis = new Rickshaw.Graph.Axis.Y.Scaled( {"
		."		graph: graph,"
		."		orientation: 'left',"
		."		tickFormat: Rickshaw.Fixtures.Number.formatKMBT,"
		."		scale: d3.scale.linear().domain(["
		.		$s->getMin().", ".$s->getMax()."]).nice(),"
		."		element: document.getElementById('".$s->getSymbol()."_y_axis'),"
		."	} );"	
		."	var legend = new Rickshaw.Graph.Legend({"
		."		graph: graph,"
		."		element: document.getElementById('".$s->getSymbol()."_legend')"
		."	});"
		."	new Rickshaw.Graph.HoverDetail({"
		."	  graph: graph"
		."	});"
		."	graph.render();"
		."		</script>";
		echo $text;
	}
*/
    echo "</table>";
	
	
	
/* 	*************************************************************
	start database exercise.
	*************************************************************
*/




/*

investments=# \d
             List of relations
 Schema |      Name      | Type  |  Owner   
--------+----------------+-------+----------
 public | historicquotes | table | devonmcb
 public | purchases      | table | devonmcb

investments=# select * from purchases where symbol = 'TRIP';

 symbol |       name        | quantity | purchdate  | pricepershare 
--------+-------------------+----------+------------+---------------
 TRIP   | TripAdvisor, Inc. |       55 | 2014-05-22 |         87.64
 
 
investments=# insert into historicquotes values ('WOOF', '2014-05-22', 9.99);
INSERT 0 1

investments=# select * from historicquotes;
 symbol |  thedate   | closevalue 
--------+------------+------------
 WOOF   | 2014-05-22 |         10
 
You can insert multiple rows in a single command:

INSERT INTO products (product_no, name, price) VALUES
    (1, 'Cheese', 9.99),
    (2, 'Bread', 1.99),
    (3, 'Milk', 2.99);
 
*/


	$dbconn = pg_connect("host=localhost dbname=investments user=php password=Susquehanna") or die('connection failed');
/*WORKS:	
	// Insert works fine, but has to be unique each time (for Symbol)
	$query = "INSERT INTO purchases VALUES"
    ."		('EF', 'EF, Scheisters', 33, '2014-05-22', 9.99),"
    ."		('GH', 'GH, Inc.', 22, '2014-05-22', 1.99)";	
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	echo "<pre>";
	print_r($result); // just prints 'resource id'
	echo "</pre>";
*/
	
/*WORKS:	
	// Insert works fine, but has to be unique each time (for Symbol)
	$query = "INSERT INTO historicquotes VALUES"
    ."		('ATT', '2014-04-22', 9.99),"
    ."		('GE', '2014-04-22', 1.99),"
    ."		('HP', '2014-04-22', 2.99)";	
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	echo "<pre>";
	print_r($result); // just prints 'resource id'
	echo "</pre>";
*/
		
	// Performing SQL query
	//$query = "select * from purchases where symbol = 'TRIP'";
	$query = "select * from purchases";
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	
	// can't just do the following b.c. it's an object thingy
	//echo $result;
		
	// Printing results in HTML
	echo "<table>\n";
	while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
		echo "\t<tr>\n";
		foreach ($line as $col_value) {
			echo "\t\t<td>$col_value</td>\n";
		}
		echo "\t</tr>\n";
	}
	echo "</table>\n";

	// Free resultset
	pg_free_result($result);

	// Closing connection
	pg_close($dbconn);

/* 	*************************************************************
	end database exercise.
	*************************************************************
*/

	
	
	
	
	echo "</body>";
	echo "</html>";

?>
