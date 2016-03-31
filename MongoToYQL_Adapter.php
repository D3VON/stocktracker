<?php  // MongoToYQL_Adapter.php

/****************************************************************************
 * This class provides an API to accomplish two things:
 * 1. query YQL;
 * 2. do C.R.U.D. operations on the local MongoDB database.
 * 
 * Using database: 'investments' (or 'test' for development)
 * Using collections: 'stocks', and 'history'
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

		if(array_key_exists(0, $resultArray) && $resultArray[0] === 0){ // 2nd test prolly not necessary
			return $resultArray;
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
	
		$theArray = array();
	
		$theArray[$theStock["symbol"]] = $theStock; // this is the convention that conforms to later parsing of this structure.
		
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
	

	
	
	
	
	/** Query the Mongo data store for all stocks owned by that user.
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
		
		$theStocks = array();
		foreach ($cursor as $document) {
			$theStocks[] = $document;
		}
		
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
		
		/*************************************************************/
		// 1. Grab stock info from YQL
		/*************************************************************/
		$y = new YQL;
		$thePurchase = array();

		/****************************************************************************
		 *  SET UP DATABASE CONNECTION FOR USE THROUGHOUT THE REST OF THIS SCRIPT
		*****************************************************************************/
		/* NOTE: YQL class receives JSON from YQL query, but converts the JSON 
		 * into a PHP variable (a multi-dimensional array), so it's easy to weedle out 
		 * pieces you need with nice PHP operators. 
		 */
		//$resultArray = $y->getQuote($symbol);// DISCOVERED WE DON'T NEED THIS AT ALL; REMOVING. iT IS in fact misleading b.c. could add a purchase from last week, but it querying quote a week later, when we actually added it to the db would needlessly save data from the date of the add, not from the date of the actual purchase. Stuff like Name can be obtained when table needs quote.
		$db = $this->dbconn->selectDB("test");
		$collection = $db->stocks; 
			
		/* YQL gives superfluous data, so just take what we want from the JSON object,
		 * and save in a local array (need to cast it from "stdClass Object" to array.
		 		*/
		//$thePurchase = $resultArray['query']['results']['quote']['Name'];// DISCOVERED WE DON'T NEED THIS AT ALL; REMOVING
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

		if(array_key_exists(0, $yql) && $yql[0] === 0){ // 2nd test prolly not necessary
			echo "woofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOFwoofWOOF";
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
		//	echo "<pre>"; print_r($mongo); echo "</pre>";
		$yql = $this->fetchFromYQL($mongo);
		//	echo "<pre>"; print_r($yql); echo "</pre>";


		if(array_key_exists(0, $yql) && $yql[0] === 0){ // 2nd test prolly not necessary
			return $yql;
		}else{
			//echo "finishing----MongoToYQL_Adapter:getAllStocksByOwner<br>";
			return $this->combineYQLandMongoArrays($mongo, $yql);
		}
	}
}


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

