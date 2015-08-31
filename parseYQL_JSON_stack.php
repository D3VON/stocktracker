<!doctype html> 
<html>
	<head>
		<title> Testing Rickshaw / D3 tools to make graphs </title>
		<meta charset="utf-8">

		<!-- dumb mistake: putting "/" at beginning of source files doesn't mean 'this directory' -->
		<link rel="stylesheet" href="rickshaw.min.css">
		<script src="d3.min.js"></script> 
		<script src="d3.v2.js"></script>
		<script src="d3.layout.min.js"></script> 
		<script src="rickshaw.min.js"></script> 

		<style>
			#chart_container {
					position: relative;
					font-family: Arial, Helvetica, sans-serif;
			}
			#chart {
					position: relative;
					left: 40px;
			}
			#y_axis {
					position: absolute;
					top: 0;
					bottom: 0;
					width: 40px;
			}
		</style>
		
	</head>
 
	<body>
		
		<div id="legend"></div>
		<div id="chart_container">
			<p> </p>
				<div id="y_axis"></div>
				<div id="chart"></div>
		</div>

<?php		

	$colors = array("#0000FF", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33");

	$stocks = array("YHOO", "AAPL", "FB","GOOG","GOOGL", "TGT", "HD", "AMZN", "ESRX");
	$quantities = array(60,6, 190,4,4,27,18,6,30);
	$purchDate = array("YHOO", "AAPL", "FB", "GOOG","GOOGL", "TGT", "HD", "AMZN", "ESRX");

	// 'beta-setup' for dynamic input
	
	//echo 'Curl: ', function_exists('curl_version') ? 'Enabled' : 'Disabled';
	
// perhaps load into an associative array: symbol is key, values are an object
// ( so far just two data members / fields in the proposed object. --therefore... maybe have 
// a 3D array thus: $symbol[0][0], with no further data.  So, declare like this (?)	 $symbol[1][1]
	
	$date = new DateTime(); // get today's date
	//echo $date->format('Y-m-d') . "<br>";
	//echo $date->getTimestamp();//returns UTC
		
	$startDate = "2014-06-15";
	//$endDate = "2014-05-05"; // last business day, or current price
	$endDate = $date->format('Y-m-d'); // today's date
	
	
	
$cashFlag = 0; // flag = 0 if "cash string of dates" not built yet
foreach($stocks as $s){
			
	$symbol = array_pop($quantities);
	
	/*
		PROBLEM HERE: YQL returns *previous* business day (if you're expecting to get
		today's data included.  For example, even though it's 5:41 PM (markets are closed) 
		I'm only getting data from previous business day.  Even though I give 
		the query today's date.  Ugh.  So, I guess I'd need to run another 
		yql query to add today's current stock value to any graph I'm making.  
		
		I WANT CURRENT VALUE!!!  NOT STALE VALUE!!!  WTF YQL?!
	*/

	$yqlQuery = "https://query.yahooapis.com/v1/public/yql" 
				. "?q=select%20*%20from%20yahoo.finance.historicaldata%20"
				. "where%20symbol%20%3D%20%22"
				. $s . "%22%20and%20startDate%20%3D%20%22"
				. $startDate . "%22%20and%20endDate%20%3D%20%22"
				. $endDate . "%22&format=json&diagnostics=true&env=store"
				. "%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";

	$amt = 	array_pop($quantities);	

	// simple file_get_contents WILL NOT WORK on d3von.com to fetch from yahoo.com
	// originally I was fetching from a yql result I saved locally by hand.
	//$json_string = file_get_contents("yql");
	
	
	/*verify cURL works
	function cURLcheckBasicFunctions() 
	{ 								
	  if( !function_exists("curl_init") && 
		  !function_exists("curl_setopt") && 
		  !function_exists("curl_exec") && 
		  !function_exists("curl_close") ) return false; 
	  else return true; 
	} 	
	if (cURLcheckBasicFunctions())
	{ 
		echo "<br>curl's OK<br>"; 
	}else{
		echo "<br>curl's not OK<br>";
	}
	*/
	//echo "rest query: " . $yqlQuery . "<br>";
	
	
/*=============================================================================
	Using cURL is an absolutely horrible way to grab a web site.  For several 
	reasons:
	1. won't work on d3von server.  (neither will simple file_get_contents();
	2. I was going to say I have to write to a file, but figured out how to 
	   write to a variable instead.
	3. Here are a ton of curl_setopt arguments you can set and I don't care about
	   all that annoyance.  Ugh. 
	
	*/
	$ch = curl_init($yqlQuery);
	if($ch) 
	{ 
		if( !curl_setopt($ch, CURLOPT_URL, $yqlQuery) ) 
		{ 		
			curl_close($ch); // to match curl_init() 
			echo "FAIL: curl_setopt(CURLOPT_URL)"; 
		} 
		if( !curl_setopt($ch, CURLOPT_HEADER, 0) ) 
		{
			echo "FAIL: curl_setopt(CURLOPT_HEADER)"; 
		} 
		if( !curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0) ) 
		{
			echo "FAIL: curl_setopt(CURLOPT_SSL_VERIFYHOST)"; 
		} 
		if( !curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0) ) 
		{
			echo "FAIL: curl_setopt(CURLOPT_SSL_VERIFYPEER)"; 
		} 
		if( !curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1) ) 
		{
			echo "FAIL: curl_setopt(CURLOPT_FOLLOWLOCATION)"; 
		} 
		// flag to pass result to variable instead of to a file.
		if( !curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) ) 
		{
			echo "FAIL: curl_setopt(CURLOPT_RETURNTRANSFER)"; 
		}
		if( !($response = curl_exec($ch)) ) 
		{
			echo "FAIL: curl_exec()"; 
			echo '<br>Curl error: ' . curl_error($ch);
		} 			
		curl_close($ch); 
	} 
	else
	{
		echo "FAIL: curl_init()"; 
	}	

	//$json_string = file_get_contents("yql.txt");
	//echo "<br>rest query result: " . $json_string . "<br>";
		
	$recordArr = json_decode($response);
	//$recordArr = json_decode($json_string);
	//$data1 = "{ ";
	//$notFirst = 0;
	foreach($recordArr->query->results->quote as $Q){
		//if(0 < $notFirst++){ $data1 = ", { "; }
		//echo $notFirst++;
		$dt = strtotime($Q->Date);//good news! rickshaw uses seconds, not milliseconds!
		$arr1[] = "{ x: $dt, y: ". $amt*$Q->Close ." },";
		
		// ADMITTEDLY SLOPPY, BUT THIS IS THE BUILDING PHASE.....
		// HERE, i'M setting up 'cash' to plot on the graph.  
		// I'll have to do better than this in order to blend cash in where needed
		// to show historic moves.  Perhaps handle it when future circumstances
		// arise in order to make my life easier now. 
		$cashArr[] = "{ x: $dt, y: 1592.23 },";
	} 
	
	$priceData = "";
	while($arr1 != NULL){
		$priceData .= array_pop($arr1);	
	}
	
	// hold strings of stock data for each stock (an array of strings)
	$stocksPriceData[] = $priceData;
	
	// build a string for javascript to plot cash onto the graph.  
	// looks like this: { x: 2014-05-09, y: 1592.23 },{ x: 2014-05-10, y: 1592.23 },etc...
	if($cashFlag != 0)
	{
		// FYI I loaded $cashArr with "dummy" values in the block above here. 
		$elements0 = "";
		while($cashArr != NULL){
			$elements0 .= array_pop($cashArr);	
		}
		$cashFlag = 99;
	}
}	
		
	/***********************************************************************
	 ***********************************************************************
	 ***********************   OLD STYLE: not used anymore *****************
	 ***********************************************************************
	 ***********************************************************************
	 ***********************************************************************/
	 /*
	$symbol = "FB";

	$yqlQuery = "https://query.yahooapis.com/v1/public/yql" 
				. "?q=select%20*%20from%20yahoo.finance.historicaldata%20"
				. "where%20symbol%20%3D%20%22"
				. $symbol . "%22%20and%20startDate%20%3D%20%22"
				. $startDate . "%22%20and%20endDate%20%3D%20%22"
				. $endDate . "%22&format=json&diagnostics=true&env=store"
				. "%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";

	echo "rest query: " . $yqlQuery . "<br>";
	
	$ch = curl_init($yqlQuery);
	if($ch) 
	{ 
			if( !curl_setopt($ch, CURLOPT_URL, $yqlQuery) ) 
			{ 		
				echo "<br> 5 <br>";
				//fclose($fp); // to match fopen() 
				curl_close($ch); // to match curl_init() 
				echo "FAIL: curl_setopt(CURLOPT_URL)"; 
			} 
			//if( !curl_setopt($ch, CURLOPT_FILE, $fp) ) 
			//{
			//	echo "<br> 6 <br>";
			////	echo "FAIL: curl_setopt(CURLOPT_FILE)"; 
			//}
			if( !curl_setopt($ch, CURLOPT_HEADER, 0) ) 
			{
				echo "<br> 7 <br>";
				echo "FAIL: curl_setopt(CURLOPT_HEADER)"; 
			} 
			if( !curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0) ) 
			{
				echo "<br> 7a <br>";
				echo "FAIL: curl_setopt(CURLOPT_SSL_VERIFYHOST)"; 
			} 
			if( !curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0) ) 
			{
				echo "<br> 7b <br>";
				echo "FAIL: curl_setopt(CURLOPT_SSL_VERIFYPEER)"; 
			} 
			if( !curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1) ) 
			{
				echo "<br> 7c <br>";
				echo "FAIL: curl_setopt(CURLOPT_FOLLOWLOCATION)"; 
			} 
			// necessary so as to pass result to variable instead of to a file.
			if( !curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) ) 
			{
				echo "<br> 7d <br>";
				echo "FAIL: curl_setopt(CURLOPT_RETURNTRANSFER)"; 
			}
			echo "<br>heading into curl_exec thing.....<br>";
			if( !($response = curl_exec($ch)) ) 
			{
				echo "<br> 8 <br>";
				echo "FAIL: curl_exec()"; 
				echo '<br>Curl error: ' . curl_error($ch);
			} 
			echo "<br>done with curl_exec thing.....<br>";
			
			curl_close($ch); 
			echo "<br> 10 <br>";
			//fclose($fp); 
			//echo "<br> 11 <br>";
			//echo "SUCCESS: yql.txt "; 
		//} 
		//else 
		//{	
		//	echo "<br> 12 <br>";
		//	echo "FAIL: fopen()"; 
		//}
	} 
	else
	{
		echo "<br> 13 <br>";	
		echo "FAIL: curl_init()"; 
	}	
	echo "<br> 14 <br>";

	//$json_string = file_get_contents("yql.txt");
	//echo "<br>rest query result: " . $json_string . "<br>";
		
	$recordArr = json_decode($response);
	//$recordArr = json_decode($json_string);
	//$data1 = "{ ";
	//$notFirst = 0;
	foreach($recordArr->query->results->quote as $Q){
		//if(0 < $notFirst++){ $data1 = ", { "; }
		//echo $notFirst++;
		$dt = strtotime($Q->Date);//good news! rickshaw uses seconds, not milliseconds!
		$arr2[] = "{ x: $dt, y: ". 60*$Q->Close ." },";
	} 
	$elements2 = "";
	while($arr2 != NULL){
		$elements2 .= array_pop($arr2);	
	}
	
	
	*/
	
	
	/***********************************************************************
	 ***********************************************************************
	 ***********************************************************************
	 ***************   very old style: not used anymore ********************
	 ***********************************************************************
	 ***********************************************************************/
	 //ERASE THIS BLOCK;ERASE THIS BLOCK;ERASE THIS BLOCK;ERASE THIS BLOCK;ERASE THIS BLOCK;
	 //ERASE THIS BLOCK;ERASE THIS BLOCK;ERASE THIS BLOCK;ERASE THIS BLOCK;ERASE THIS BLOCK;
	 //ERASE THIS BLOCK;ERASE THIS BLOCK;ERASE THIS BLOCK;ERASE THIS BLOCK;ERASE THIS BLOCK;
	 // ERASE THIS BLOCK; BUT: I prefer this way of getting a URL.  
	 // however: right now it's getting a local file.  file_get_contents() can 
	 // get a URL with so much less code than cURL.  Ugh!
	/* OLD WAY: REPLACED BY cURL way just above here. (between huge **** blocks)
	$json2 = file_get_contents("yql_fb");//Facebook FB
	$recordArr2 = json_decode($json2);
	foreach($recordArr2->query->results->quote as $Q){
				
		$dt = strtotime($Q->Date);//good news! rickshaw uses seconds, not milliseconds!
		$arr2[] = "{ x: $dt, y: ". 100*$Q->Close ." },";
	}	
	$elements2 = "";
	while($arr2 != NULL){
		$elements2 .= array_pop($arr2);	
	}
	echo "2nd: $elements2 END<br>";	
	//$data2 .= "{ x: 23, y: 40 }, { x: 24, y: 49 }, { x: 25, y: 38 }, { x: 26, y: 30 }, { x: 27, y: 32 }";
	*/
?>
		<script>
			var graph = new Rickshaw.Graph( {
				element: document.querySelector("#chart"),
				width: 600,
				height: 400,
				renderer: 'area',
				stroke: true,
			
				series: [ 
								
				<?php 
					$commaFlag = 0;
					foreach($stocksPriceData as $d)
					{					
						if($commaFlag != 0)
						{ 
							echo ","; 
						}
						$color = array_pop($colors);
						$symb = array_pop($stocks);
						echo "{	data: [  $d ], color: '$color', name: '$symb'}";
						$commaFlag = 1;
					}
					echo "] } );";
				?>	
			
			var xAxis = new Rickshaw.Graph.Axis.Time({
				graph: graph,
				timeFixture: new Rickshaw.Fixtures.Time(),
				//timeUnit: days
			});
			
			//var xAxis = d3.svg.axis().scale(x).orient("bottom").tickFormat(d3.time.format("%m-%d"));
			
			var yAxis = new Rickshaw.Graph.Axis.Y( {
				graph: graph,
				orientation: 'left',
				tickFormat: Rickshaw.Fixtures.Number.formatKMBT,
				element: document.getElementById('y_axis'),
			} );
			
			var legend = new Rickshaw.Graph.Legend({
				graph: graph,
				element: document.querySelector('#legend')
			});
			graph.render();
			
			//var axes = new Rickshaw.Graph.Axis.Time({
			//	graph: graph
			//});
			//axes.render();


		</script>
	</body>
</html>