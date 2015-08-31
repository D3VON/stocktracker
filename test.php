<?php


	error_reporting(E_ALL);
	
	echo "woof";
	
	$arr[] = 6;
	$arr[] = 7;
	$arr[] = 8;
	echo "<br>";
	echo "<br>";

	//print_r($arr);
	$fruit = array_pop($arr);
	print_r($fruit);
	//print_r($arr);
	$fruit = array_pop($arr);
	print_r($fruit);
	//print_r($arr);
	$fruit = array_pop($arr);
	print_r($fruit);
	echo "<br>";
	echo "<br>";
	print_r($arr);
	
	$recordArr = array("2014-04-25","2014-04-24","2014-04-23","2014-04-22","2014-04-21");
	$data1 = "{ ";
	$notFirst = 0;
	
	foreach($recordArr as $Q){
		if(0 < $notFirst++){ $data1 = ", { "; }
		
		$dt = strtotime($Q);//good news! rickshaw uses seconds, not milliseconds!
		$data1 .= " x: $dt, y: $notFirst }";
		
		$arr1[] = $data1;
	}
	
	while($arr1 != NULL){
		echo array_pop($arr1);	
	}
	
	echo "<br>";
echo 'Curl: ', function_exists('curl_version') ? 'Enabled' : 'Disabled';
	$symbol = "YHOO";
	$startDate = "2014-04-24";
	$endDate = "2014-05-02"; // last business day, or current price

	$restQuery = "https://query.yahooapis.com/v1/public/yql" 
				. "?q=select%20*%20from%20yahoo.finance.historicaldata%20"
				. "where%20symbol%20%3D%20%22"
				. $symbol . "%22%20and%20startDate%20%3D%20%22"
				. $startDate . "%22%20and%20endDate%20%3D%20%22"
				. $endDate . "%22&format=json&diagnostics=true&env=store"
				. "%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
				
				
	var_dump($restQuery);

	$json_string = file_get_contents($restQuery);//Yahoo YHOO

	var_dump($json_string);



?>