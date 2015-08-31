<?php

require_once('Stock.php');

/*********************************
 *********************************
 ******** set up one obj *********
 ********************************* 
 *********************************/

$bar = new Stock;
$bar->setSymbol("HD");
$bar->setQuantity(1);
$bar->setStartDate("2014-04-24");
$bar->setAccount("Fidelity IRA");
//$bar->setEndDate($ed); USE ONLY IF DIFFERENT FROM CURRENT (obj will automatically find current)

echo "<br>";
echo $bar->getSymbol();
echo "<br>";
echo $bar->getQuantity();
echo "<br>";
echo $bar->getStartDate();
echo "<br>";
echo $bar->getData();
echo "<br>";
echo $bar->getAccount();
echo "<br>-------------------------<br>";

/*********************************
 ******** set up many obj ********
 *********************************/

$stockNames = array("YHOO","AAPL","GOOG");
$amt = 1;
$stockObjects = array();
foreach($stockNames as $x){
	$theStockObj = new Stock();
	$theStockObj->setSymbol($x);
	$theStockObj->setQuantity($amt);
	$theStockObj->setStartDate("2014-04-24");
	$theStockObj->setAccount("ScotTrade IRA");
	$stockObjects[] = $theStockObj;
} 

echo "<br> ..............doing multiple stocks in an array of objects.........<br>";

foreach($stockObjects as $woof){
	
	echo "<br>getting Symbol: ";
	echo $woof->getSymbol();
	echo "<br>getting Quantity: ";
	echo $woof->getQuantity();
	echo "<br>getting StartDate: ";
	echo $woof->getStartDate();
	echo "<br>getting Data: ";
	echo $woof->getData();
	echo "<br>getting Account name: ";
	echo $woof->getAccount();
	echo "<br>-------------------------<br>";
}

$text = ""
."<script>"
."	var graph = new Rickshaw.Graph( {"
."		element: document.querySelector("#chart"),"
."		width: 200,"
."		height: 140,"
."		renderer: 'line',"
."		stroke: true,"	
."		series: [ "
					
$commaFlag = 0;
foreach($stocksPriceData as $d)
{					
	if($commaFlag != 0)
	{ 
		. ","; 
	}
	$color = array_pop($colors);
	$symb = array_pop($stocks);
	. "{	data: [  $d ], color: '$color', name: '$symb'}";
	$commaFlag = 1;
}
echo "] } );";
	
."	var xAxis = new Rickshaw.Graph.Axis.Time({"
."		graph: graph,"
."		timeFixture: new Rickshaw.Fixtures.Time(),"
."		//timeUnit: days"
."	});"
."	var yAxis = new Rickshaw.Graph.Axis.Y( {"
."	graph: graph,"
."		orientation: 'left',"
."		tickFormat: Rickshaw.Fixtures.Number.formatKMBT,"
."		element: document.getElementById('y_axis'),"
."	} );"	
."	var legend = new Rickshaw.Graph.Legend({"
."		graph: graph,"
."		element: document.querySelector('#legend')"
."	});"
."	graph.render();"
."		</script>"
."</body>"
."</html>"

?>
