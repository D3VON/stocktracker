<!doctype html> 
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>StockTracker -- keeping track of your stock purchases</title>
		<link rel="stylesheet" href="css/reset.css">
<!-- 		
		<link rel="stylesheet" href="rickshaw.min.css">
 -->
		<link type="text/css" rel="stylesheet" href="css/stocks.css">
		
		<link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css">
		<!--script src="js/bootstrap.min.js"></script -->	
		<style type="text/css">
			.bullbear {
			    display: block;
			    margin-left: auto;
			    margin-right: auto; }
			.centertitle {
			    display: block;
			    margin-left: auto;
			    margin-right: auto; }
			.centertable {
			    display: block;
			    margin-left: auto;
			    margin-right: auto; }
			#the_add_form > *{
			    font-size:12px;
			    margin-bottom:15px;
			}
			#the_add_form > p > label{
			    display:block;
			    font-size:12px;
			}
			#the_add_form > p > input, #the_add_form > p > textarea{
			        width:100px;
			}    
		</style>
		
		
	
	</head>
	<body>
		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/jquery-ui.min.js"></script>
		<script src="js/stocksApp.js"></script>
	

		<h1 class="centertitle">Stock Tracker -- "My Account"</h1>

		<div>	
			<div><img  class="bullbear" src="images/bullbear.gif" alt="bull and bear"></div>
			<p>This is a tool I'm developing for people (aka: me) who own stocks in several different accounts, and they all present them differently, and it's very annoying to have to log into different places and see different presentations of what's happening with each stock. This page will be where I enter each purchase.  From the data captured here, I will be able to do cool graphing and maybe pull in news articles and ratings of the stocks.  Stuff like that.</p>
			<p>NOTE: no effort has been made (as of yet) to make this page presentable, or responsive.  Thank you.</p>
		</div>
<!-- 			 		
		<form id="getOwner" action=""> 
			Owner name: <input type="text" id="ownerName" value="guest">
			<input id="theOwnerButton"  type="submit" value="Specify Owner">
		</form>
-->
 		
<?php		

require_once('../MongoToYQL_Adapter.php');
require_once('StocksTable.php');
	
	$owner = "me";	
	//echo "<br>Owner is: " . $owner . "<br>";
	
	//echo "<div id=\"thetable\" class=\"centertable\">";
	
		if($owner == ""){
			echo "<br>No owner specified.  Please input an owner.<br>";
		}else{			
			try{
				$mongo = new MongoToYQL_Adapter;
			} catch (Exception $e) {
				echo 'Caught exception: ',  $e->getMessage(), "<br>";
			}
			
			try{
				$tableMaker = new StocksTable;
			} catch (Exception $e) {
				echo 'Caught exception: ',  $e->getMessage(), "<br>";
			}
			
			//$owner = "me";
			$stocks = $mongo->getAllStocksByOwner($owner);
			//echo "<p>the owner is: " . $owner . "</p>";
			echo $tableMaker->makeStocksTable($owner,$stocks);
			
		}
	//echo "</div>";
	

?>
		
	
</body>
</html>



<!-- ?php	 // this entire section is a place to keep old code I don't want to throw out.

	/* COOL: NOT USED HERE for the time being
	 * but could be cool to keep it.  Has to do with rickshaw charting. 
	 * I particularly like the VARIABLE-VARIABLE-looking things, using stock symbols to 
	 * keep them unique versus eachother. 
	 * ----------that assumes you don't have multiple purchases of the same stock. Ugh. 
	 * 
	
	// symbols provided for scripts to 'chew on' and test
	$stockNames = array("TRIP","GOOGL","GOOG","TGT","AAPL","LNKD");//,"GS","ADM","XES","AMZN","CAT","CHK","CTXS","DE","DG","DIS","KS","DPZ","EBAY","ED","EEM","FB","FDX","FFIV","FOSFX","GE","GIS","GLW","HD","HTA","IBM","JMBA","KBH","LOW","MCD","MSFT","ORCL","PCAR","RVBD","SLB","TOL","VMW","VWIGX","WFM","ANF","AVP","BBY","C");
	
	//set up unique style tags/blocks
	//for each graph in header
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


/*
 // capital Stock.php is the class
require_once('Stock.php');
//$colors = array("#0000FF", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33");
$date = new DateTime(); // get today's date
$startDate = "2014-06-01";
$endDate = $date->format('Y-m-d'); // today's date

$amt = 1;
$min = 9999;
$stockObjects = array();// set above, this is just for making data to test
foreach($stockNames as $symb){

// make each stock object. The stock object handles getting the data.
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
."<div id=\"".$symb."_chart_container\">"
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





}

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
echo "</table>";

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
*/

//require_once('../MongoToYQL_Adapter.php');
//require_once('StocksTable.php');
// 	try{\t\t<
// 		$tableMaker = new StocksTable;
// 	} catch (Exception $e) {
// 		echo 'Caught exception: ',  $e->getMessage(), "<br>";
// 	}



?> -->



<!-- 
		<script src="d3.min.js"></script> 
		<script src="d3.v2.js"></script>
		<script src="d3.layout.min.js"></script> 
		<script src="rickshaw.min.js"></script> 
 -->

