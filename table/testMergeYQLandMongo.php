<?php  // testMergeYQLandMongo.php


	/*	This script tests functionality:
	 *  (design issue: same symbol can be in Mongo many times, but only once from YQL) 
	 * 	- query Mongo for a list of symbols owned by the user
	 *  - use that list to query YQL for current stock info
	 *  - merge that current result of YQL into existing symbols found in Mongo

	*/

	error_reporting(E_ALL);

	include_once('../YQL.php');//incude sends warning if fails, require is fatal.
	include_once('../ColumnIdentity.php');

	
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
	
   /* Loop over the $mongoArray to discover which stock symbols the user has.
	* Then call the appropriate YQL fetching function
	* 
	* NOTE: if copying this function: it relies on two other functions!!
	* 
	*/
	function fetchFromYQL(&$mongoArray){
		
		$len = count($mongoArray);
		// $type = ($len > 1) ? "many" : "single";
		// echo $type;
		
		// get the symbols and make the string for the YQL query
		$symbolsString = "";
		for ($i = 0; $i < $len; $i++){
			$symbolsString .= $mongoArray[$i]["symbol"];
			if ($i+1 < $len){ $symbolsString .= "%22%2C%22"; }
			// example of what I'm trying to form here: 
			// YHOO%22%2C%22AAPL%22%2C%22GOOG%22%2C%22MSFT
			// (Opening and closing quotes (%22) are not needed because
			// they are already in the pre-built YQL query in the YQL class.)
		}	

		if ($len > 1){
			return fetchManyFromYQL($symbolsString);
		}else{
			return fetchOneFromYQL($symbolsString);
		}
	}
	
	/** 
	 * Fetch quotes from YQL for the given list of symbols. Return an associative
	 * array of arrays. The key is stock symbols (obviously unique), the value is
	 * an array being the fields of the quote (including redundantly the symbol). 
	 * 
	 * Rationale for making it this way: I need to merge the user-specific data of
	 * stock purchases with this current stock data, and being able to access current 
	 * data with an efficiency of 1 is optimal: e.g., scenario: many records of 
	 * purchases of the same stock; access current data of that stock reliably. 
	 * 
	 * @param $symbolsString  a YQL-acceptible string of symbols to search for
	 *
	 * @return $newArray An associative array of arrays of stock quotes, keyed by symbol
	 */
	function fetchManyFromYQL($symbolsString){
		
		// set up YQL connection to get current stock info
		$y = new YQL;
	
		// NOTE: This YQL class receives JSON from YQL query, but converts the JSON
		// into a PHP variable (a multi-dimensional array), so it's easy to weedle out
		// pieces you need with nice PHP operators.
		$resultArray = $y->getQuote( $symbolsString );
	
		// YQL gives superfluous data, so just take what we want from the JSON object,
		// and save in a local array (need to cast it from "stdClass Object" to array.	
		$theArray = $resultArray->{'query'}->{'results'}->{'quote'};
		
		$newArray = array();
		// This is an Array of stdClass Objects, so each stdClass Object should
		// be cast to an array, so we have a multidimensional array.
		$len = count($theArray);
		for ($i = 0; $i < $len; $i++){ 
			$value = (array)$theArray[$i];
			$key = $value["symbol"];
			// make a map, using unique field 'symbol' as key
			$newArray[$key] = $value;
		}	
	
		return $newArray;
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
	
	
	
	
	
	/** Merge data from MongoDB[i] with data from YQL[j] for i and j elements in
	 *  both arrays into a new, multidimensional array containing n elements.
	 *
	 *  Also: do computed fields like total-current-value (quant + price)
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
	function combineYQLandMongoArrays(&$mongo, &$yql){
		$newArray = array();
	
		$len = count($mongo);	
		$s = array(); // the stock
						 	
		/*notetoself: calculated fields are calculated in StocksTable.php
		 * That cannot continue.  I'm calculating them here as I receive them
		* from the db instead.
		// never used: $dollarchange = $s['LastTradePriceOnly'] - $s['purchaseprice'];
		*/
		foreach($mongo as $m){
			$s["symbol"] = $m["symbol"];
			$s["AverageDailyVolume"] 	= $yql[$m["symbol"]]["AverageDailyVolume"];
			$s["Change"] 				= $yql[$m["symbol"]]["Change"];
			$s["DaysLow"] 				= $yql[$m["symbol"]]["DaysLow"];
			$s["DaysHigh"] 				= $yql[$m["symbol"]]["DaysHigh"];
			$s["YearLow"] 				= $yql[$m["symbol"]]["YearLow"];
			$s["YearHigh"] 				= $yql[$m["symbol"]]["YearHigh"];
			$s["MarketCapitalization"] 	= $yql[$m["symbol"]]["MarketCapitalization"];
			$s["LastTradePriceOnly"] 	= $yql[$m["symbol"]]["LastTradePriceOnly"];
			if ($s['LastTradePriceOnly'] == 0){
				$s["percentchangetoday"] = "no value";
			}else{
				$s["percentchangetoday"]	= $yql[$m["symbol"]]["Change"] / $s['LastTradePriceOnly'] * 100;
			}
			$s["DaysRange"] 			= $yql[$m["symbol"]]["DaysRange"];
			$s["Name"] 					= $yql[$m["symbol"]]["Name"];
			$s["Volume"] 				= $yql[$m["symbol"]]["Volume"];
			$s["StockExchange"] 		= $yql[$m["symbol"]]["StockExchange"];
			$s["_id"] 					= $m["_id"];
			$s["purchasedate"] 			= $m["purchasedate"];
			$s["purchasequantity"] 		= $m["purchasequantity"];
			$s["purchaseprice"] 		= $m["purchaseprice"];
			$s["purchasefee"] 			= $m["purchasefee"];
			$s["purchasetotal"] 		= $m["purchasefee"] + ($m["purchaseprice"] * $m["purchasequantity"]);
			$s["account"] 				= $m["account"];
			$s["owner"] 				= $m["owner"];
			$s["totalCurrentValue"] 	= $s["purchasequantity"] * $s['LastTradePriceOnly'] - $s['purchasefee'];
			$s["totalChangeDollar"] 	= $s["totalCurrentValue"] - $s["purchasetotal"];
			$s["totalChangePercent"]	= $s["totalChangeDollar"] / $s["purchasetotal"] * 100;
		
			$newArray[] = $s;
		}
	
		return $newArray;	
	}




	/**Open MongoDB connection
	 * loop through given JSON result passed as argument
	 * saving each date's data in a separate query to Mongo
	 */
	function addNewHistoryToMongo($symbol){

		$symbol = strtouppwer($symbol); // unfortuately, I need to do this everywhere $symbol is passed.
		//Annoying: YQL defaults to uppers, but, I have a lot of data already stored locally as lowers.
		//...so I decided in a slip-shod fashion to go with lowers for everything local. Ugh.

		//$yql = new YQL_forTesting();
		$yql = new YQL();

		// NOTE: This YQL class receives JSON from YQL query, but converts the JSON
		// into a PHP variable (a multi-dimensional array), so it's easy to weedle out
		// pieces you need with nice PHP operators.
		$resultArray = $yql->populateHistoricalData($symbol);
		//echo "<pre>"; var_dump($resultArray); echo "</pre>";
		/* WATCH OUT!  This was formed by querying YQL several times (once for each year), so
		   it contains a separate multi-dimensional array for each year. You'll have to
		   foreach loop through each year to weedle out the contiguous info you want.
		 */

		/****************************************************************************
		 *  SET UP DATABASE CONNECTION
		 *****************************************************************************/
		/* NOTE: YQL class receives JSON from YQL query, but converts the JSON
		 * into a PHP variable (a multi-dimensional array), so it's easy to weedle out
		 * pieces you need with nice PHP operators.
		 */
		$dbconn = new MongoClient();
		$db = $dbconn->selectDB("test");
		$collection = $db->history;

		/* YQL gives superfluous data, so just take what we want from the JSON object,
		 * and save in a local array (need to cast it from "stdClass Object" to array.
		 */
		$theHistory['symbol'] = $symbol;
		$theHistory['day'] = array(); // to hold each day's data

		/* This damned thing takes like 60 seconds to run. */
		// many JSON elements in that array (representing many whole years worth of quotes)
		/* MongoDB version of capturing many years worth of a stock's price */
		foreach($resultArray as $json){

			//testing: just print count of days in that year to show that something happened (if not showing data for each day)
			$index = $json['query']['count'] - 1;
			echo "days in that year: $index<br>";

			//foreach ($json->query->results->quote as $Q){
			for($index; $index >= 0; $index--){

				/* instead of making an insert query for each date like in PostgreSQL,
				   we instead make a huge JSON object, and shove that into the
				   MongoDB document store. So, if you query a symbol, you'll get the
				   whole shebang.
				 */
				$day = array(); // make a new one each loop.

				$day['date']         = $json['query']['results']['quote'][$index]['Date'];
				$day['volume']       = $json['query']['results']['quote'][$index]['Volume'];
				$day['closingprice'] = $json['query']['results']['quote'][$index]['Close'];
				//$theHistory['day'] = $day; // since $theHistory['day'] is an array, I believe it will simply append $day to it.
				array_push($theHistory['day'], $day); // I don't think we need this level of formality

			}

			// Have to add a special array to contain the data for the last day.
			// Back-story: Mongo doesn't permit you to peek at the last element of an array.
			// You can remove the last element, but you can't know what you removed
			// without loading the entire document (which here is very large) into a local
			// instance. But you can peek at a specially-made (what do you call it?) data member
			// designed to hold the last element in the array in question. Bitches!
			$lastday = array();
			$lastday['date']         = $json['query']['results']['quote'][0]['Date'];
			$lastday['volume']       = $json['query']['results']['quote'][0]['Volume'];
			$lastday['closingprice'] = $json['query']['results']['quote'][0]['Close'];
			$theHistory['lastday'] = $lastday;
		}

		//echo "<pre>"; var_dump($theHistory); echo "</pre>";

		/* The PHP MongoDB Driver accepts only PHP arrays for inserts and queries
		 * (see here: http://www.php.net/manual/en/mongo.queries.php)
		 */
		$collection->insert($theHistory);

	}
	/* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  TEST TEST TEST TEST TEST TEST   TEST TEST TEST TEST TEST TEST   */
	//addNewHistoryToMongo("spy");
	//addNewHistoryToMongo("xhb");
	//addNewHistoryToMongo("bid");
	//addNewHistoryToMongo("wfm");
	//// ---------------------to clean up after that test if necessary-------------
	//echo "<pre>"; var_dump(getSymbolsFromHistory()); echo "</pre>";
	//notInHistory("goog");
	//notInHistory("googl");
	//notInHistory("aapl");
	//notInHistory("bac");   <--------this function is no longer in this script
	//notInHistory("bwp");
	//notInHistory("lng");
	//notInHistory("cmg");
	////removeFromMongo($symbol);   <--------this function is no longer in this script
	//notInHistory("fb");
	//notInHistory("aapl");
	//notInHistory("ibm");
	//notInHistory("zx");



















	
	function populateOneHistoryFromYQL($symbol){
		$dbconn = new MongoClient();
		$db = $dbconn->selectDB("test");
		$collection = $db->histories; // this is an apocryphal data store.  The "real" one is history
		
		// check if symbol exists already
		$findThis = array('symbol' => $symbol);
		
		// return only 'symbol', not '_id' ('_id' is always returned by default), so tell it not to.
		$returnThis = array("symbol" => 1, "_id" => 0);
		$cursor = $collection->find($findThis, $returnThis);
		$foundIt = $cursor->getNext()['symbol'];
// 		echo "<pre>"; print_r($foundIt); echo "</pre>";
// 		echo "-----------------------------------------------------<br>";
		
// 		// TESTING: verifying only one in the whole collection
// 		foreach ($cursor as $document) {
// 			echo "<br>-->" .  $document['symbol'] . "<br>";
// 		}
// 		echo "-----------------------------------------------------<br>";
		
		// if not already in the collection, query YQL and save to collection
		if ($foundIt !== $symbol){
			$historyArray = array();
			// set up YQL connection to get current stock info
			$y = new YQL;
		
			$yqlResult = $y->populateHistoricalData( $symbol );
			
			foreach($yqlResult as $year){
				
				while( !empty($year['query']['results']['quote']) ){
					$record = (array)array_pop($year['query']['results']['quote']);
					//grab date and convert to Unix, use as key to assoc. array
					$historyArray[strtotime($record['Date'])] = $record;			
				}
			}
			
			$finishedArray = array (
					"symbol" => $symbol, 
					"history" => $historyArray
			);
			
			
			/* The PHP MongDB Driver accepts only PHP arrays for inserts and queries
			 * (see here: http://www.php.net/manual/en/mongo.queries.php)
			 * So you need to convert any JSON to an array.
			 */
			$collection->insert($finishedArray);
			//return $newArray;
		}
	}
	
	
	
	/** Query Mongo for a list of stocks in the histories collection.
	 * Only indicate how far back to go; will return up to current date.
	 *
	 * @param $list an array: the list of the stocks
	 * @param $goBack the number of days back to go (absolute days, not business days)
	 *
	 * @return $theStocksArray an associative array .......unfinished........
	 */
	function queryMongoHistories($symbol, $goBack){ // =  time())
		
		$start = time() - (60 * 60 * 24 * $goBack);
		$diff = time() - $start;
		
		$dbconn = new MongoClient();
		// get connection to db
		$db = $dbconn->selectDB("test");
		$collection = $db->histories;
		
		$findThisSymbol = array('symbol' => $symbol);
		//$aboveThisDate = array("age" => array('$gt' => 33))       array('$gt' => $goBack)
		//$sortByThis = array('Name' => 1 );// +1 is ascending order
		$cursor = $collection->find($findThisSymbol); //->sort($sortByThis);

		$dates = $cursor->getNext();
		/* NOTE: I'm unhappy with how I'm just getting the entire 15 years worth of data
		 * because I don't know how to query this data structure using PHP/Mongo. I'd
		 * like instead to query a date range, but don't know how. If I try to solve this
		 * in future, I should start by looking at what's returned:
		 */
		//echo "<pre>"; print_r($dates); echo "</pre>";
		
		foreach($dates['history'] as $unixDate => $dataArray){
//			echo "<br>$start   $unixDate<br>";
			
			if($unixDate > $start){
				echo $dataArray['Date']; 
				echo "<br>";
			}
		}
	
	
	}
	
	
	
		
	$owner = "guest66";
	$arrayOfStocksFromMongo = queryMongoMany($owner);
	// echo "<pre>"; print_r($arrayOfStocksFromMongo); echo "</pre>";
	foreach($arrayOfStocksFromMongo as $s){ echo $s['symbol'] . "<br>"; }
	 
// 	$arrayOfStocksFromYQL = fetchFromYQL($arrayOfStocksFromMongo);
// 	//echo "<pre>"; print_r($arrayOfStocksFromYQL); echo "</pre>";
	
// 	$finalArray = combineYQLandMongoArrays($arrayOfStocksFromMongo, $arrayOfStocksFromYQL);
	
// 	echo "<pre>"; print_r($finalArray); echo "</pre>";
	
	$symbol = "fds";
	/* @return  array 	array of JSON objects, each containing one year of quotes,
	*								but last element contains this YTD
	*/
	populateOneHistoryFromYQL($symbol);	
	//echo "<pre>"; print_r($history); echo "</pre>";	
	echo "<br>Time: " . time() . "<br>";
	
	
	queryMongoHistories($symbol, 90);  // spits out dates
	
	
	
?>
	