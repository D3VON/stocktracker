<?php  // MongoToYQL_Adapter.php

/****************************************************************************
 * This class provides an API to accomplish two things:
 * 1. query YQL;
 * 2. do C.R.U.D. operations on the local MongoDB database.
 * 
 * Using database: 'investments' (or 'test' for development)
 * Using collections: 'stocks', and 'history' --  and apocryphal 'histories'
 *
 * WARNING: BE VERY HESITANT TO REMOVE ANY STOCK HISTORY, AS OTHER USERS MAY BE USING IT
 *  
 */

// handles querying YQL
include_once('YQL.php');//incude sends warning if fails, require is fatal.

class MongoToYQL_Adapter {
	/**************************************************************************
	 Data members
	**************************************************************************/
	private $dbconn;
	//private $result; // UNUSED: WHY IS THIS HERE?
	
	/**
	 * Class constructor.
	 */
	function __construct()
	{
		/****************************************************************************
		 *  SET UP DATABASE CONNECTION FOR USE THROUGHOUT THE REST OF THIS SCRIPT
		*****************************************************************************/
		
		$this->dbconn = new MongoClient();// when I was having problems, try/catch was uselessly silent, so screw it!

	}
	
	// to test to see if the object instantiation works and a method can be called.
	function dummy(){ return "@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@"; }
	
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
	 * @param String $symbolsString  a YQL-acceptible string of symbols to search for
	 *
	 * @return array $newArray An associative array of arrays of stock quotes, keyed by symbol
	 */
	function fetchManyFromYQL($symbolsString){
		//echo $symbolsString;
		// set up YQL connection to get current stock info
		$y = new YQL;
	
		// NOTE: This YQL class receives JSON from YQL query, but converts the JSON
		// into a PHP variable (a multi-dimensional array), so it's easy to weedle out
		// pieces you need with nice PHP operators.
		$resultArray = $y->getQuote( $symbolsString );

		//var_dump($resultArray);

		if(!array_key_exists('query', $resultArray)){ // 2nd test prolly not necessary
			return  "<br>MongoToYQL_Adapter line " . __LINE__ . "<br />" . var_dump($resultArray); // Magic constant
		}
		if(array_key_exists(0, $resultArray) && $resultArray[0] === 0){ // 2nd test prolly not necessary
			return  "<br>MongoToYQL_Adapter line " . __LINE__ . "<br />" . var_dump($resultArray); // Magic constant
		}
	
		// YQL gives superfluous data, so just take what we want from the JSON object,
		// and save in a local array (need to cast it from "stdClass Object" to array.	
		//$theArray = $resultArray->{'query'}->{'results'}->{'quote'};
		$theArray = $resultArray['query']['results']['quote'];
		
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
		
		//echo "finishing function: MongoToYQL_Adapter:fetchManyFromYQL<br>";
		return $newArray;
	}
	
	
	function fetchOneFromYQL($symbol){
		//echo $symbol;
	
		// set up YQL connection to get current stock info
		$y = new YQL;
	
		// Get current data on that stock from YQL
		// NOTE: This YQL class receives JSON from YQL query, but converts the JSON
		// into a PHP variable (a multi-dimensional array), so it's easy to weedle out
		// pieces you need with nice PHP operators.
		$resultArray = $y->getQuote( $symbol );

		if(array_key_exists(0, $resultArray) && $resultArray[0] === 0){ // 2nd test prolly not necessary
			return $resultArray;
		}
	
		// Grab only the part we want,
		// Cast from "stdClass Object" to array.
		$theStock = (array)$resultArray['query']['results']['quote'];

		//var_dump($theStock);
		if(empty($theStock)){
			return "Sorry, there are no stocks for that user at this time.";

		}
	
		$theArray = array();
		//var_dump($theArray);
		$theArray[$theStock["symbol"]] = $theStock;
		
		//echo "finishing function: MongoToYQL_Adapter:fetchOneFromYQL<br>";
		return $theArray;
	}
	/*
	 // YQL gives superfluous data, so just take what we want from the JSON object,
	// and save in a local array (need to cast it from "stdClass Object" to array.
			$theArray = $resultArray->{'query'}->{'results'}->{'quote'};
	
	
	
			// Grab only the part we want,
			// Cast from "stdClass Object" to array.
			//$theStock = $resultArray['query']['results']['quote'];
	
	
			//$key = $value["symbol"];
		 */
	
	
   /* Loop over the $mongoArray to discover which stock symbols the user has.
	* Then call the appropriate YQL fetching function
	* 
	* NOTE: if copying this function: it relies on two other functions!!
	* 
	*/
	function fetchFromYQL(&$mongoArray){
		
		$len = count($mongoArray);
		 // TEST: $type = ($len > 0) ? "many" : "single";
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
		
		//echo "finishing function: MongoToYQL_Adapter:fetchFromYQL<br>";
		if ($len > 1){
			return $this->fetchManyFromYQL($symbolsString);
		}else{
			return $this->fetchOneFromYQL($symbolsString);
		}
	}
	

	/** Get all stocks owned by owner -- just purchase info, not current quote.
	 *  Query the Mongo data store for all stocks owned by that user.
	 *
	 * @param String $owner the owner of the stocks
	 *
	 * @property mixed I HAVE NO IDEA HOW TO DOCUMENT "$db->stocks" TO SATISFY PHPDOC HERE
	 *
	 * @return array $theStocksArray a complete array the stocks owned by the given user
	 */
	 function queryMongoMany($owner){
	
		$dbconn = new MongoClient();
		// get connection to db
		$db = $dbconn->selectDB("test");
		$collection = $db->stocks;
		
		$findThis = array('owner' => $owner);
		$sortByThis = array('Name' => 1 );// +1 is ascending order
		
		$cursor = $collection->find($findThis)->sort($sortByThis);
		//var_dump($cursor);
		$theStocks = array();
		foreach ($cursor as $document) {
			//echo "<br>Now document in cursor:<br>";
			//var_dump($document);
			$theStocks[] = $document;
		}
		 //var_dump($theStocks);
		//echo "finishing function: MongoToYQL_Adapter:queryMongoMany<br>";
		//returns beautiful array of arrays
		return $theStocks;
	
	}
	
	
	 /** Merge data from MongoDB[i] with data from YQL[i] for all n elements in
	 *  both arrays into a new, multidimensional array containing n elements.
	 *
	 * @param array $mongo an array of arrays containing a user's information about
	 *               specific stock purchases
	 *               WARNING: $mongo must be of type Array( [0] => Array ... )
	 *
	 * @param array $yql   an array of arrays containing info queried from YQL.
	 *               WARNING: $yql must be of type Array( [0] => Array ... )
	 *
	 * @return array $theStocksArray a complete array of arrays of the stocks owned by
	 *                         a user (used to populate the stocktracker table)
	 function combineYQLandMongoArrays($mongo, $yql){
		 $newArray = array();
		
		 $len = count($mongo);
		 // potentially many purchases of same symbol means $mongo could be longer
		 // than $yql array.  
		
		 $stock = array();
			
		 for ($i = 0; $i < $len; $i++){ // can't use foreach b.c. need to refer back to orig. element
		 	if (isset($yql[$i]) && isset($mongo[$i])) {
				$stock["symbol"] 				= $yql[$i]["symbol"];
				$stock["AverageDailyVolume"] 	= $yql[$i]["AverageDailyVolume"];
				$stock["Change"] 				= $yql[$i]["Change"];
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
				$stock["account"] 				= $mongo[$i]["account"];
				$stock["owner"] 				= $mongo[$i]["owner"];
		 	}
			 $newArray[$i] = $stock;
		 }
		
		 return $newArray;
	
	 }
	 */
	
	/** add a stock purchase to our database
	 *
	 *
	 * @param String $symbol the stock ticker symbol
	 * @param String $date the date it was purchased
	 * @param String $quantity the quantity purchased of the symbol
	 * @param String $price the price it was purchased
	 * @param String $fee the fee that was paid to purchase the thing
	 * @param String $account the account where this stock will live
	 * @param String $owner the owner of the stock
	 * 
	 * @return array $theStocks -- via a call to member function getAllStocksByOwner($owner)
	 */
	function addPurchase($symbol, $quantity, $price, $date, $fee, $account, $owner){

		$stockinfo = $this->fetchOneFromYQL($symbol);
		$thePurchase = array();
		$thePurchase['symbol'] = $symbol;
		$thePurchase['Name'] = $stockinfo[$symbol]["Name"]; // why do I ever need this?!
		$thePurchase['purchasedate'] = $date;
		$thePurchase['purchasequantity'] = $quantity;
		$thePurchase['purchaseprice'] = $price;
		$thePurchase['purchasefee'] = $fee;
		$thePurchase['account'] = $account;
		$thePurchase['owner'] = $owner;
		
		/*******************************************************
		 *         ADD SYMBOL (YHOO) TO THE DATABASE
		 *******************************************************/
		/* The PHP MongDB Driver accepts only PHP arrays for inserts and queries
		 * (see here: http://www.php.net/manual/en/mongo.queries.php)
		* So you need to convert your JSON to an array.
		*/
		/****************************************************************************
		 *  SET UP DATABASE CONNECTION FOR USE THROUGHOUT THE REST OF THIS SCRIPT
		 *****************************************************************************/
		$db = $this->dbconn->selectDB("test");
		$collection = $db->stocks;
		$collection->insert($thePurchase);
		
		//echo "finishing function: MongoToYQL_Adapter:addPurchase<br>";
		return $this->getAllStocksByOwner($owner);
	}
		
	
	
	/** remove one or more stocks from the db
	 *
	 * @param array $remove an array of ids (the MongoDB-generated ids of the documents) to be removed
	 * @param String $owner the owner of the document (the stock)
	 *
	 * @property mixed I HAVE NO IDEA HOW TO DOCUMENT "$db->stocks" TO SATISFY PHPDOC HERE
	 *
	 * @return array $theStocksArray an array of PHP arrays of the stocks owned by that dude
	 */
	function removePurchase($remove, $owner){
	
		//echo "<br>WE'RE IN MongoToYQL_Adapter, calling removePurchase()<br>";
		//var_dump($owner);
		
		/****************************************************************************
		 *  SET UP DATABASE CONNECTION FOR USE THROUGHOUT THE REST OF THIS SCRIPT
		*****************************************************************************/
		$db = $this->dbconn->selectDB("test");
		$collection = $db->stocks;
		
		/* If it's the whole table, user may have chose the 'all' checkbox, so that would
		 * just an artifact I don't really need (because jQuery would then have checked
		 * all the boxes, and POSTed all the specific ids). However, we could perhaps
		 * instead have done a query to remove all by user.  I feel this is a bit safer
		 * to remove the specific ids returned by the calling script, though. 
		 */ 
		if ($remove[0] == 'all') { array_shift($remove); }
		
		//echo "<br>WE'RE IN MongoToYQL_Adapter, calling removePurchase()<br>";
		//var_dump($owner);
		//var_dump($remove);
		// do the deed
		foreach ($remove as $id){
			/* REVELATION: MongoDB embeds its ids into a special little MongoID object
			 * --those sons a bitches.
			 */
			$collection->remove( array("_id" => new MongoId($id)) );

			// definitely don't remove history, as other users may be using that stock.
		}
		
		//echo "finishing function: MongoToYQL_Adapter:removePurchase<br>";
		return $this->getAllStocksByOwner($owner);
	}
	
	
	/** edit one stock in the db
	 *
	 * TRICK: I'm removing the original record, then adding it back with the new
     * 		  information, rather than modifying the original record. 
	 *
	 * @param String $id the id (the MongoDB-generated id of the document) to be edited
	 * @param String $symbol the symbol to be edited
	 * @param String $quantity the quantity to be edited
	 * @param String $price the price (each) to be edited
	 * @param String $date the date to be edited
	 * @param String $fee the fee to be edited
	 * @param String $account the account to be edited
	 * @param String $owner the owner of the document (the stock)
	 *
	 * @property mixed I HAVE NO IDEA HOW TO DOCUMENT "$db->stocks" TO SATISFY PHPDOC HERE
	 *
	 * @return array $theStocksArray a refreshed array of PHP arrays of the stocks owned by that dude
	 * 
	 */
	function editPurchase($id, $symbol, $quantity, $price, $date, $fee, $account, $owner){
	
		//echo "<br>WE'RE IN MongoToYQL_Adapter, calling removePurchase()<br>";
		//var_dump($owner);
	
		/****************************************************************************
		 *  SET UP DATABASE CONNECTION FOR USE THROUGHOUT THE REST OF THIS SCRIPT
		*****************************************************************************/
		$db = $this->dbconn->selectDB("test");
		$collection = $db->stocks;
		
		/* REVELATION: MongoDB embeds its ids into a special little MongoID object
		 * NOTE: just wipe out the original and replace it with a new one, rather 
		 * than mucking around 'editing' it, which seems ridiculously tedious in Mongo.
		*/
		$collection->remove( array("_id" => new MongoId($id)) );

		
		//echo "finishing function: MongoToYQL_Adapter:editPurchase<br>";
		return $this->addPurchase($symbol, $quantity, $price, $date, $fee, $account, $owner);
	}
	

	/** Merge data from MongoDB[i] with data from YQL[j] for i and j elements in
	 *  both arrays into a new, multidimensional array containing n elements.
	 *
	 *  Also: do computed fields like total-current-value (quant + price)
	 *
	 * @param array $mongo an array of arrays containing a user's information about
	 *               specific stock purchases
	 *               WARNING: $mongo must be of type Array( [0] => Array ... )
	 *
	 * @param array $yql   an array of arrays containing info queried from YQL.
	 *               WARNING: $yql must be of type Array( [0] => Array ... )
	 *
	 * @return array $theStocksArray a complete array of arrays of the stocks owned by
	 *                         a user (used to populate the stocktracker table)
	 */
	function combineYQLandMongoArrays(&$mongo, &$yql){

		/* inadequate test here: YQL typically returns *something* when the query doesn't work */
		if(!is_array($yql)){
			echo "<br>MongoToYQL_Adapter 416 YQL failure.  Huh.<br>";
			var_dump($yql);
			return $yql;
		}

		/* inadequate test here: YQL typically returns *something* when the query doesn't work */
		if(array_key_exists(0, $yql) && $yql[0] === 0){
			echo "<br>MongoToYQL_Adapter 423 YQL failure.  Huh.<br>";
			var_dump($yql);
			return $yql;
		}

		$newArray = array();
	
		//$len = count($mongo);
		//echo $len;
		$s = array(); // the stock
		//$agrigateChangeDollars = 0;
	
		/* notetoself: calculated fields are calculated in StocksTable.php
		 * That cannot continue.  I'm also calculating them here as I receive them
		 * from the db.
		 */
		// never used: $dollarchange = $s['LastTradePriceOnly'] - $s['purchaseprice'];
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
			$s["totalChangePercent"]	= $s["purchasetotal"] > 0 ? $s["totalChangeDollar"] / $s["purchasetotal"] * 100 : 0;
	
			$newArray[] = $s;
		}
		//echo "finishing function: MongoToYQL_Adapter:combineYQLandMongoArrays<br>";
		//echo "<pre>"; print_r($s); echo "</pre>";
		return $newArray;
	}

	
	function getAllStocksByOwner($owner){ //,$sortby){
		//echo "starting----MongoToYQL_Adapter:getAllStocksByOwner; owner is $owner <--------------------------------!<br>";
		$mongo = $this->queryMongoMany($owner); // $mongo is an array of arrays

		//owner might not exist in mongo

		//var_dump($mongo);

		/*	if owner has no stocks, I have to handle that gracefully! */
		//var_dump($theStock);
		if(empty($mongo)){
			return "Sorry, there are no stocks for that user at this time.";
		}


		//	echo "<pre>"; print_r($mongo); echo "</pre>";
		$yqlarray = $this->fetchFromYQL($mongo);
		//	echo "<pre>"; print_r($yql); echo "</pre>";


		if(is_array($yqlarray) && array_key_exists(0, $yqlarray) && $yqlarray[0] === 0){
			echo "There was trouble in MongoToYQL_Adapter on line " . __LINE__ . "<br />"; // Magic constant
			var_dump($yqlarray);
			return $yqlarray; // this would be where an error would be detected....................I STILL NEED TO WORK ON THIS!!!!!
		}
		//echo "finishing----MongoToYQL_Adapter:getAllStocksByOwner<br>";
		return $this->combineYQLandMongoArrays($mongo, $yqlarray);
	}


	/**
	 *  Query the Mongo data store for all owners.
	 *
	 * @return array $owners all owners that show up in the stocks data store
	 */
	function getAllOwners(){

		$dbconn = new MongoClient();
		// get connection to db
		$db = $dbconn->selectDB("test");
		$collection = $db->stocks;

		$aNiceArrayNotACursor = $collection->distinct("owner");

		return $aNiceArrayNotACursor;
	}



	function getSymbolsOfThisOwnerALLPurchases($owner){

		$dbconn = new MongoClient();
		$db = $dbconn->selectDB("test");
		$collection = $db->stocks;

		$result = array();
		$findwhere = array("owner" => $owner);
		$getthis = array("symbol" => 1); // that is, just the symbol
		$symbols = $collection->find($findwhere,$getthis);

		foreach ($symbols as $document) {
			$result[] = $document["symbol"];
		}
		return $result;
	}

	function getSymbolsOfThisOwnerDISTINCT($owner){

		$dbconn = new MongoClient();
		$db = $dbconn->selectDB("test");
		$collection = $db->stocks;

		$findwhere = array("owner" => $owner);
		$matchthis = array('$match'=>$findwhere);
		/* This is flagrantly nasty MongoDB syntax. the _id field is mandatory (seems to be an id made-up on the fly
		 * for each item in the group).  But more despicable: the field you're trying to group by (in my case 'symbol'
		 * must have a dollar sign affixed to it, and it must be in quotes. So, rather than just say
		 * something like "groupby symbol" I have to do the following cryptic mess: */
		$groupby = array('$group'=>array('_id' => '$symbol'));
		$sortthisway = array('$sort'=>array('_id'=>1));//_id was created by $group operation

		// ->distinct cannot sort.  ->distinct is just a convenience version of ->aggregate
		// Would have been clearer to use distinct instead of aggregate, then sort with PHP.
		//$symbols = $collection->distinct("symbol",$findwhere);

		$mongoresult = $collection->aggregate(array($matchthis,$groupby,$sortthisway));

		// mongo returns *such* a rat's nest of JSON structure, like this:
		//		Array
		//		( [result] => Array
		//			( [0] => Array ( [_id] => aapl )  [1] => Array ( [_id] => bac )
		//			)
		//			[ok] => 1
		//		)
		if($mongoresult['ok']){ // should be 1
			$result = array();
			foreach($mongoresult['result'] as $symbol){ // unMongo-ify
				$result[] = $symbol['_id'];
			}
			return $result;
		}else{
			return $mongoresult['ok'];
		}
	}


	function getHistory($symbol){
		$dbconn = new MongoClient();
		$db = $dbconn->selectDB("test");
		$collection = $db->history;
		$temp = array();
		$datesAndValues = array();

		$findThis = array('symbol' => $symbol);

		$doc = $collection->findOne($findThis);
		// INTERESTING: find returns a cursor, findOne returns a nice array.

		if(empty($doc)) {
			echo "That stock doesn't exist"; // how to handle this error? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? ?
		} else {
			foreach($doc["day"] as $day){
//				$temp["date"] = $day["date"];
//				$temp["closingprice"] = $day["closingprice"];
//				$datesAndValues[] = $temp;
				// ALTERNATIVE STRUCTURE: "date" is key whose value is "closingprice"

// Here is where I manually manipulate the period that shows up on my rickshaw graphs
//				$numberOfMonths = 23.4;
//				$start = 1456790400 - (86400*30*$numberOfMonths);
//
//				// QUICK FIX FOR TESTING: LIMITING WHAT'S RETURNED: ONLY SINCE 3/1/16 (1456790400 IN UNIX) --- very brittle, server side bad code
//				if($start<strtotime($day["date"])) {
					$datesAndValues[$day["date"]] = $day["closingprice"];
//				}
				//$datesAndValues[] = $temp;
			}
								// for testing: get rid of this when done:
//								$temp[] = $symbol;
//								end($datesAndValues);
//								$temp[] = key($datesAndValues);
//								array_pop($datesAndValues);
			return $datesAndValues;
		}
	}
	/* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  TEST TEST TEST TEST TEST TEST   TEST TEST TEST TEST TEST TEST   */
//echo getHistory("fb");



	/** Build a string of coordinate points for a D3 graph.
	 *
	 * Note: Arguments conform to PHP's strtotime()-acceptable arguments
	 * Note: Original PostgreSQL version is in InvestDB.php
	 *
	 * @param $symbol 		string stock symbol
	 * @param $numPeriods  	int number of [days | months | years] of period
	 * @param $typePeriods 	string type of period [days | months | years]
	 * @param $endDate 		string end date of the graph
	 *
	 * @return array containing D3 graph information as follows:
	 *					1. string of the symbol
	 * 					2. string of coordinates
	 *					3. int of min bound of graph
	 *					4. int of max bound of graph
	 */
	function getD3Coordinates($symbol, $numPeriods, $typePeriods, $endDate, $quant=1)
	{
		// build the start date from arguments given (calculated back from $endDate)
		$startDate = date('Y-m-d', strtotime("-$numPeriods $typePeriods", strtotime($endDate)));

		$history = $this->getHistory($symbol);

		// set up min/max for Y axis bounds
		// also store (x,y) coordinates to $priceData string
		$min = 99999;
		$max = 0;
		$priceData = "";
		//while ($row = pg_fetch_row($result)) {
		foreach($history as $date => $value){

			// for the life o' me, I can't remember why I wanted to multiply by $quant
//			if ($min > $quant*$value) // if value lower than current min, then min becomes current lower value
//			{
//				$min = $quant*$value;
//			}
//			if ($max < $quant*$value)
//			{
//				$max = $quant*$value;
//			}

			if ($min > $value) // if value lower than current min, then min becomes current lower value
			{
				$min = $value;
			}
			if ($max < $value)
			{
				$max = $value;
			}

			$dt = strtotime($date);//nice! rickshaw uses seconds, not milliseconds!
			$priceData .= "{ x: $dt, y: ". $value ." },";
		}

		$coordInfo = array();
		$coordInfo['symbol'] = $symbol;
		$coordInfo['coords'] = $priceData;
		$coordInfo['min'] = number_format(($min-($min/20)),2, '.', '');// english notation without thousands separator
		$coordInfo['max'] = number_format(($max+($max/20)),2, '.', '');// english notation without thousands separator
		return $coordInfo;
	}


	/**Open MongoDB connection
	 * loop through given JSON result passed as argument
	 * saving each date's data in a separate query to Mongo
	 */
	function addNewHistoryToMongo($symbol){

		$symbol = strtoupper($symbol);
		$yql = new YQL();

		// NOTE: This YQL class receives JSON from YQL query, but converts the JSON
		// into a PHP variable (a multi-dimensional array), so it's easy to weedle out
		// pieces you need with nice PHP operators.
		$resultArray = $yql->populateHistoricalData($symbol);
		//echo "<pre>"; var_dump($resultArray); echo "</pre>";
		/* Note!  This was formed by querying YQL several times (once for each year), so
		   it contains a separate multi-dimensional array for each year. You'll have to
		   foreach loop through each year to weedle out the info you want.
		 */

		/****************************************************************************
		 *  SET UP DATABASE CONNECTION
		 *****************************************************************************/
		/* NOTE: YQL class receives JSON from YQL query, then converts the JSON
		 * into a PHP variable (a multi-dimensional array), so it's easy to weedle out
		 * pieces you need with nice PHP operators.
		 */
		$dbconn = new MongoClient();
		$db = $dbconn->selectDB("test");
		$collection = $db->history;

		$theHistory['symbol'] = $symbol;
		$theHistory['day'] = array(); // to hold each day's data

		/* This damned thing takes like 60 seconds to run. */
		// many JSON elements in that array (representing many whole years worth of quotes)
		/* MongoDB version of capturing many years worth of a stock's price */
		foreach($resultArray as $json){

			//testing: just print count of days in that year to show that something happened (if not showing data for each day)
			$index = $json['query']['count'] - 1;
			//echo "days in that year: $index<br>";

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




	/* DEPRICATED -- uses histories data store, not history data store.
	   Note: this version has many more fields for each day than the history data store.
	   Stocktracker initially used the simpler history data store.
	 */
	function populateOneHistoryFromYQL($symbol){

		// always ensure symbols are upper case
		$symbol = strtoupper($symbol);

		$dbconn = new MongoClient();
		$db = $dbconn->selectDB("test");
		$collection = $db->histories;

		// check if symbol exists already
		$findThis = array('symbol' => $symbol);

		// return only 'symbol', not '_id' ('_id' is always returned by default), so tell it not to.
		$returnThis = array("symbol" => 1, "_id" => 0);
		$cursor = $collection->find($findThis, $returnThis);
		$foundIt = $cursor->getNext()['symbol'];
						// 		echo "<pre>"; print_r($foundIt); echo "</pre>";
						// 		echo "-----------------------------------------------------<br>";
						// 		// TESTING: verifying if there is only one of the given symbol in the whole collection
						// 		foreach ($cursor as $document) {
						// 			echo "<br>-->" .  $document['symbol'] . "<br>";
						// 		}
						// 		echo "-----------------------------------------------------<br>";

		// if not already in the collection, query YQL and save to collection
		// else, it's already there, so skip everything.
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


} // end of class

/* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  TEST TEST TEST TEST TEST TEST   TEST TEST TEST TEST TEST TEST   */
/* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  TEST TEST TEST TEST TEST TEST   TEST TEST TEST TEST TEST TEST   */
/* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  TEST TEST TEST TEST TEST TEST   TEST TEST TEST TEST TEST TEST   */
/* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  TEST TEST TEST TEST TEST TEST   TEST TEST TEST TEST TEST TEST   */
/* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  TEST TEST TEST TEST TEST TEST   TEST TEST TEST TEST TEST TEST   */
/* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  TEST TEST TEST TEST TEST TEST   TEST TEST TEST TEST TEST TEST   */
/* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  TEST TEST TEST TEST TEST TEST   TEST TEST TEST TEST TEST TEST   */
/* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  TEST TEST TEST TEST TEST TEST   TEST TEST TEST TEST TEST TEST   */
/* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  TEST TEST TEST TEST TEST TEST   TEST TEST TEST TEST TEST TEST   */
/* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  TEST TEST TEST TEST TEST TEST   TEST TEST TEST TEST TEST TEST   */
//$testTheClass = new MongoToYQL_Adapter();
//$symbol = 'aapl';

// testing getAllOwners()
//echo "<pre>"; print_r($testTheClass->getAllOwners()); echo "</pre>";

//foreach($testTheClass->getAllOwners() as $owner){
//	foreach($testTheClass->getSymbolsOfThisOwner($owner) as $symbol){
//
//		echo $symbol . "<br>";
//		echo "<pre>"; print_r($testTheClass->getHistory($symbol)); echo "</pre>";
//
//
//	}
//}

//$result = $testTheClass->getD3Coordinates("hd", 3, "months", date('Y-m-d') );
//echo "<pre>"; print_r($result); echo "</pre>";



//echo "<pre>"; var_dump($testTheClass->getHistory("aapl")); echo "</pre>";
//echo "<pre>"; print_r($testTheClass->getHistory("aapl")); echo "</pre>";

//$owner = 'me';
//echo "<pre>"; print_r($testTheClass->getSymbolsOfThisOwnerDISTINCT($owner)); echo "</pre>";








/*	for sorting:

        const Name 					= 0;
        const Symbol 				= 1;
        const DollarChangeToday 	= 2;
        const PercentChangeToday 	= 3;
        const TotalCost 			= 4;
        const TotalDollarChange 	= 5;
        const TotalPercentChange	= 6;
        const Account 				= 7;
        const PurchaseDate 			= 8;

*/


/*

	Here is commandline query: > db.stocks.find({owner: 'me', symbol: 'yhoo'})
	(cleaned up a bit)

	{ 	"_id" : ObjectId("55dc16dd5490c40254dba668"), 
		"symbol" : "yhoo", 
		"AverageDailyVolume" : "12495100", 
		"Change" : "-1.62", 
		"DaysLow" : "29.00", 
		"DaysHigh" : "32.28", 
		"YearLow" : "29.00", "YearHigh" : "52.62", 
		"MarketCapitalization" : "29.47B", 
		"LastTradePriceOnly" : "31.31", 
		"DaysRange" : "29.00 - 32.28", 
		"Name" : "Yahoo! Inc.", 
		"Symbol" : "yhoo", 
		"Volume" : "23163378", 
		"StockExchange" : "NMS", 
		"purchasedate" : "04/29/2014", 
		"purchasequantity" : "60", 
		"purchaseprice" : "35.75", 
		"purchasefee" : "7", 
		"account" : "ScottradeIRA", 
		"owner" : "me" 
	}

*/

		
				/*
				 echo "<pre>";
				print_r($JSON);
				echo "</pre>";
					
				Here is the JSON object (the pertinent piece)
	
				[results] => stdClass Object
				(
						[quote] => stdClass Object
						(
								[symbol] => YHOO
								[AverageDailyVolume] => 20762400
								[Change] => +0.32
								[DaysLow] => 34.68
								[DaysHigh] => 35.07
								[YearLow] => 25.74
								[YearHigh] => 41.72
								[MarketCapitalization] => 35.087B
								[LastTradePriceOnly] => 34.85
								[DaysRange] => 34.68 - 35.07
								[Name] => Yahoo! Inc.
								[Symbol] => YHOO
								[Volume] => 12586865
								[StockExchange] => NasdaqNM
						)
				)
					
				select * from purchases where symbol = 'TRIP';
				symbol |       name        | quantity | purchdate  | pricepershare | fee
				--------+-------------------+----------+------------+---------------+-----
				TRIP   | TripAdvisor, Inc. |       55 | 2014-05-22 |         87.64 |
	
				*/
				
					/*
					["results"]=> object(stdClass)#3441 (1)
					{ ["quote"]=> array(252) {
					 [0]=> object(stdClass)#3442 (8) {
					["Symbol"]=> string(4) "YHOO"
					["Date"]=> string(10) "2013-12-31"
					["Open"]=> string(5) "40.17"
					["High"]=> string(5) "40.50"
					["Low"]=> string(5) "40.00"
					["Close"]=> string(5) "40.44"
					["Volume"]=> string(7) "8291400"
					["Adj_Close"]=> string(5) "40.44"
					}
					}
	
					investments=# select * from historicquotes;
					symbol |  thedate   | closevalue
					--------+------------+------------
					WOOF   | 2014-05-22 |         10
	
					*/

