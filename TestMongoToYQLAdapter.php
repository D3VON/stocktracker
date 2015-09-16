<?php  // TestMongoToYQLAdapter.php

	//woof

	/*	This script is more to prove concepts than to test MongoToYQL_Adapter.php. 
		In fact, it does not test that script. 

	*/

	error_reporting(E_ALL);

	// handles querying YQL
	include_once('YQL.php');//incude sends warning if fails, require is fatal.
	include_once('ColumnIdentity.php');//incude sends warning if fails, require is fatal.

	function AreArraysPassedByValOrRef(&$arr){
		//foreach($arr as $elem){
		$len = count($arr);
		for ($i = 0; $i < $len; $i++){  // can't use foreach b.c. need to refer back to orig. element
			$arr[$i] = 0;
		}
	}	
	
	
	function fetchOneFromYQL($symbol){
	
	
		// set up YQL connection to get current stock info
		$y = new YQL;
	
		// Get current data on that stock from YQL
		// NOTE: This YQL class receives JSON from YQL query, but converts the JSON
		// into a PHP variable (a multi-dimensional array), so it's easy to weedle out
		// pieces you need with nice PHP operators.
		$resultArray = $y->getQuote( $symbol );
	
		// Ignore superfluous data,
		// Cast from "stdClass Object" to array.
		$theStock = (array)$resultArray->{'query'}->{'results'}->{'quote'};
	
		$theArray = array($theStock); // this is the convention that conforms to later parsing of this structure.
	
		return $theArray;
	
	}

   /* Loop over the $mongoArray to discover which stock symbols the user has.
	* Then call the appropriate YQL fetching function (
	* 
	*/
	function fetchFromYQL(&$mongoArray){
		
		$len = count($mongoArray);
		// $type = ($len > 1) ? "many" : "single";
		// echo $type;
		
		// get the symbols and make the string for the YQL query
		$symbols = "";
		for ($i = 0; $i < $len; $i++){
			$symbols .= $mongoArray[$i]["symbol"];
			if ($i+1 < $len){ $symbols .= "%22%2C%22"; }
			// example of what I'm trying to form here: 
			// YHOO%22%2C%22AAPL%22%2C%22GOOG%22%2C%22MSFT
			// (Opening and closing quotes (%22) are not needed because
			// they are already in the pre-built YQL query in the YQL class.)
		}	

		if ($len > 1){
			return fetchManyFromYQL($symbols);
		}else{
			return fetchOneFromYQL($symbols);
		}

	}
	
	

	function fetchManyFromYQL($symbols){
		
	
		// set up YQL connection to get current stock info
		$y = new YQL;

		// Get current data on that stock from YQL
		// NOTE: This YQL class receives JSON from YQL query, but converts the JSON 
		// into a PHP variable (a multi-dimensional array), so it's easy to weedle out 
		// pieces you need with nice PHP operators. 
		$resultArray = $y->getQuote( $symbols );

		// YQL gives superfluous data, so just take what we want from the JSON object,
		// and save in a local array (need to cast it from "stdClass Object" to array.

 
		$theArray = $resultArray->{'query'}->{'results'}->{'quote'};
		// This is an Array of stdClass Objects, so each stdClass Object should  
		// be cast to an array, so we have a multidimensional array.
		$len = count($theArray);
		for ($i = 0; $i < $len; $i++){ // can't use foreach b.c. need to refer back to orig. array specific element
			$theArray[$i] = (array)$theArray[$i];
		}	


		return $theArray;

	}

	
	
	/** Query the Mongo data store for all stocks owned by that user. 
	 * 
	 * @param $owner the owner of the stocks
	 *
	 *
	 * @return $theStocksArray a complete array the stocks owned by the given user
	 */
	function queryMongoMany($owner){
	
		$dbconn = new MongoClient();
		// get connection to db squared away
		$db = $dbconn->selectDB("test");
		$collection = $db->stocks;
	
		$findThis = array('owner' => $owner);
		$sortByThis = array('Name' => 1 );// +1 is ascending order
	
		$cursor = $collection->find($findThis)->sort($sortByThis);
	
		$theStocks = array();
		foreach ($cursor as $document) {
			$theStocks[] = $document;
		}
	
		//returns beautiful array of arrays
		return $theStocks;
	
	}
	
	
	/** Merge data from MongoDB[i] with data from YQL[i] for all n elements in
	 *  both arrays into a new, multidimensional array containing n elements.
	 *
	 * @param $mongo an array of arrays containing a user's information about
	 *               specific stock purchases
	 *               WARNING: $mongo must be of type Array( [0] => Array ... )
	 *
	 * @param $yql   an array of arrays containing info queried from YQL.
	 *               WARNING: $yql must be of type Array( [0] => Array ... )
	 *
	 * @return $theStocksArray a complete array of arrays of the stocks owned by
	 *                         a user (used to populate the stocktracker table)
	 */
	 function combineYQLandMongoArrays($mongo, $yql){
		 $newArray = array();
		
		 $len = count($mongo);
		 if($len != count($yql)) {
		 	echo "Error retrieving data.<br>";
		 }
		
		 $stock = array();
	
		 for ($i = 0; $i < $len; $i++){ // can't use foreach b.c. need to refer back to orig. element
		 	$stock["symbol"] 				= $yql[$i]["symbol"];
		 	$stock["AverageDailyVolume"] 	= $yql[$i]["AverageDailyVolume"];
		 	$stock["Change"] 				= $yql[$i]["Change"];
		 	$stock["ChangePercent"] 		= $yql[$i]["Change"];<=============left off here
		 	$stock["DaysLow"] 				= $yql[$i]["DaysLow"];
		 	$stock["DaysHigh"] 				= $yql[$i]["DaysHigh"];
		 	$stock["YearLow"] 				= $yql[$i]["YearLow"];
		 	$stock["YearHigh"] 				= $yql[$i]["YearHigh"];
		 	$stock["MarketCapitalization"] 	= $yql[$i]["MarketCapitalization"];
		 	$stock["LastTradePriceOnly"] 	= $yql[$i]["LastTradePriceOnly"];
		 	$stock["DaysRange"] 			= $yql[$i]["DaysRange"];
		 	$stock["Name"] 					= $yql[$i]["Name"];
		 	$stock["Volume"] 				= $yql[$i]["Volume"];
		 	$stock["StockExchange"] 		= $yql[$i]["StockExchange"];
		 	$stock["_id"] 					= $mongo[$i]["_id"];
		 	$stock["purchasedate"] 			= $mongo[$i]["purchasedate"];
		 	$stock["purchasequantity"] 		= $mongo[$i]["purchasequantity"];
		 	$stock["purchaseprice"] 		= $mongo[$i]["purchaseprice"];
		 	$stock["purchasefee"] 			= $mongo[$i]["purchasefee"];
		 	$stock["purchasetotal"] 		= $mongo[$i]["purchasefee"] + ($mongo[$i]["purchaseprice"] * $mongo[$i]["purchasequantity"]);
		 	$stock["account"] 				= $mongo[$i]["account"];
		 	$stock["owner"] 				= $mongo[$i]["owner"];
	
	 		$newArray[$i] = $stock;
	 	}
	
	 	return $newArray;
	
	 }
	 
	 function getAllStocksByOwner($owner,$sortby){
	 	/*
	 	TODO:
	 	DONE $a = queryMongoMany($owner); <----modify to accept $owner argument
	 	DONE $d = fetchFromYQL($a); // clearly, $d will be compatible with $a
	 	DONE $e = combineYQLandMongoArrays($a, $d);
	 	
	 	Put into Adapter class to see if I can get that working again (without sorting yet) 
	 	
	 	- $theStocks = usort($e, sortfunction); // make a sort function
	 	- --------hopefully sort functions will be very similar to eachother.
	 
	 
	 	return $theStocks;
	 	*/
	 }
	 
	 function sortByAccount($a,$b)
	 {
	 	if ($a['account']==$b['account']) return 0;
	 	return ($a['account']<$b['account'])?-1:1;
	 }
	 
	 function sortBySymbol($a,$b)
	 {
	 	if ($a['symbol']==$b['symbol']) return 0;
	 	return ($a['symbol']<$b['symbol'])?-1:1;
	 }
	 
	//echo "Welcome to TestMongoToYQLAdapter.php, a script to test MongoToYQL_Adapter.php.<br>";

	//$mtya = new MongoToYQL_Adapter;
	 
	// hardcode to drive this function
	$owner = "guest";
	
	$a = queryMongoMany($owner);
	echo "From Mongo: <br>";
	echo "<pre>"; print_r($a[0]); echo "</pre><br>-------------------------------<br>";

	$s = $a[0]['symbol'];
	$b = fetchOneFromYQL($s);
	echo "From YQL: <br>";
	echo "<pre>"; print_r($b); echo "</pre><br>-------------------------------<br>";

	$a_prime = array($a[0]);// making one stock conform to parsing convention.
	// WARNING: Care must be taken as to if we are sending a single stock or an array of stocks
	// and what kind of structure they're embedded in.  
	// Best case: an array of stock arrays.  
	// THEREFORE: we shall make it a convention instead of a configuration each time. 
	// The convention shall be an array of stock arrays, even it it's only one stock array. 
	$c = combineYQLandMongoArrays($a_prime, $b);
	echo "Merged Mongo result with YQL result: <br>";
	echo "<pre>"; print_r($c); echo "</pre><br>-------------------------------<br>";

	$d = fetchFromYQL($a); // result of $a must be compatible with this function
	$e = combineYQLandMongoArrays($a, $d);
	echo "Merged Mongo result with YQL result: <br>";
	echo "<pre>"; print_r($e); echo "</pre><br>-------------------------------<br>";
	
	
	usort($e,"sortByAccount");
	echo "Sorted by Account: <br>";
	echo "<pre>"; print_r($e); echo "</pre><br>-------------------------------<br>";
	
	usort($e,"sortBySymbol");
	echo "Sorted by Account: <br>";
	echo "<pre>"; print_r($e); echo "</pre><br>-------------------------------<br>";
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/*
	 $array = array(1,2,3,4,5);
	echo "<pre>";  //this proves my function works just fine.
	print_r($array);
	echo "</pre>";
	echo "<br>";
	
	AreArraysPassedByValOrRef($array);
	echo "<pre>";  //this proves my function works just fine.
	print_r($array);
	echo "</pre>";
	echo "<br>";
	*/
	

/*

	for sorting: 

			    const Name 					= 0;
				const Symbol 				= 1;
				const DollarChangeToday 	= 2;
				const PercentChangeToday 	= 3;
				const TotalCost 			= 4;
				const TotalDollarChange 	= 5;
				const TotalPercentChange	= 6;
				const Account 				= 7;
				const PurchaseDate 			= 8;

	echo "<pre>";
	print_r($JSON);
	echo "</pre>";
					
	USEFUL MongoDB functions:
	========================
		$db = $this->dbconn->selectDB("test");
		$collection = $db->stocks; 
		
		$findThis = array('owner' => $owner);
		$sortByThis = array('Name' => 1 );// +1 is ascending order

		$cursor = $collection->find($findThis)->sort($sortByThis);
		
		$theStocks = array();
		foreach ($cursor as $document) {
	
			$collection->remove( array("_id" => new MongoId( $document['_id'] )) );
	
			// The PHP MongDB Driver accepts only PHP arrays for inserts and queries
			// (see here: http://www.php.net/manual/en/mongo.queries.php)
			//So you need to convert your JSON to an array.
			$collection->insert($updatedRecord);
			$theStocks[] = $updatedRecord;
		}
	END USEFUL MongoDB functions
	============================
					
*/


?>

