<?php  // MongoToYQL_Adapter.php

/****************************************************************************
 * This class provides an API to accomplish two things:
 * 1. query YQL (very specific stock information);
 * 2. do C.R.U.D. operations on the local MongoDB database.
 * 
 * Using database: 'investments'
 * Using collections: 'stocks', and 'history'
 * 
 * NOTE: bad programming practice: I have redundant code in several methods
 * to read all the stocks by owner because the DUMB THING WASN'T WORKING
 * RIGHT WHEN i JUST CALLED getAllStocksByOwner($owner).  So, it worked 
 * copying and pasting the code into each method that needed that functionality. 
 * Fucking annoying.  Never did figure out why it wouldn't work.  
 * 
 */

// handles querying YQL
include_once('YQL.php');//incude sends warning if fails, require is fatal.

class MongoToYQL_Adapter_IDONTKNOWWHYIHAVETHISCLASSTWICE {
	/**************************************************************************
	 Data members
	**************************************************************************/
	private $dbconn;
	private $result;
	
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
	
	
	/* Loop over the $mongoArray to discover which stock symbols the user has.
	 * Then call the appropriate YQL fetching function (
	 		*
	 		*/
	function fetchFromYQL(&$mongoArray){ // &$symbolsArray 
	
		$len = count($mongoArray);
		// $type = ($len > 1) ? "many" : "single";
		// echo $type;
	
		// get the symbols and make the string for the YQL query
		$symbols = "";
		$symbolsArray = array();
		for ($i = 0; $i < $len; $i++){
			$symbols .= $mongoArray[$i]["symbol"];
			
			//load array in same order as $mongo array; this is to preserve
			// a structure that will correspond with that original $mongo
			// array: YQL will discard duplicates, thereby shortening the 
			// array returned by it. We need to make the data returned by 
			// YQL conform to the original $mongo array. 
			$symbolsArray .= $mongoArray[$i]["symbol"];
			
			if ($i+1 < $len){
				$symbols .= "%22%2C%22";
			}
			// example of what I'm trying to form here:
			// YHOO%22%2C%22AAPL%22%2C%22GOOG%22%2C%22MSFT
			// (Opening and closing quotes (%22) are not needed because
			// they are already in the pre-built YQL query in the YQL class.)
		}
	
		if ($len > 1){
			return $this->fetchManyFromYQL($symbols);
		}else{
			return $this->fetchOneFromYQL($symbols);
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
	
	/** add a stock purchase to our database
	 *
	 *
	 * @param $symbol the stock ticker symbol
	 * @param $date the date it was purchased
	 * @param $quantity the quantity purchased of the symbol
	 * @param $price the price it was purchased
	 * @param $fee the fee that was paid to purchase the thing
	 * @param $owner the owner of the stock
	 * 
	 * @return $theStocks -- via a call to member function getAllStocksByOwner($owner)
	 */
	function addPurchase($symbol, $quantity, $price, $date, $fee, $account, $owner){
		
		/*************************************************************/
		// 1. Grab stock info from YQL
		/*************************************************************/
		$y = new YQL;

		/****************************************************************************
		 *  SET UP DATABASE CONNECTION FOR USE THROUGHOUT THE REST OF THIS SCRIPT
		*****************************************************************************/
		/* NOTE: YQL class receives JSON from YQL query, but converts the JSON 
		 * into a PHP variable (a multi-dimensional array), so it's easy to weedle out 
		 * pieces you need with nice PHP operators. 
		 */
		$resultArray = $y->getQuote($symbol);
		$db = $this->dbconn->selectDB("test");
		$collection = $db->stocks; 
			
		/* YQL gives superfluous data, so just take what we want from the JSON object,
		 * and save in a local array (need to cast it from "stdClass Object" to array.
		 		*/
		$thePurchase = (array)$resultArray->{'query'}->{'results'}->{'quote'};
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
		
		return $this->getAllStocksByOwner($owner);
	}
		
	
	
	/** remove one or more stocks from the db
	 *
	 * @param $remove an array of ids (the MongoDB-generated ids of the documents) to be removed
	 * @param $owners the owner of the document (the stock)
	 *
	 * @return $theStocksArray an array of PHP arrays of the stocks owned by that dude
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
		}

		return $this->getAllStocksByOwner($owner);
	}
	
	
	/** edit one stock in the db
	 *
	 * TRICK: I'm removing the original record, then adding it back with the new
     * 		  information, rather than modifying the original record. 
	 *
	 * @param $id the id (the MongoDB-generated id of the document) to be edited
	 * @param $symbol the symbol to be edited
	 * @param $quant the quantity to be edited
	 * @param $price the price (each) to be edited
	 * @param $date the date to be edited
	 * @param $fee the feeto be edited
	 * @param $account the account to be edited
	 * @param $owner the owner of the document (the stock)
	 *
	 * @return $theStocksArray a refreshed array of PHP arrays of the stocks owned by that dude
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
		 * than mucking around 'editing' it. 
		*/
		$collection->remove( array("_id" => new MongoId($id)) );

	
		return $this->addPurchase($symbol, $quantity, $price, $date, $fee, $account, $owner);
	}
	
	
	function getOwner(){
		
	}
	

	
	function getAllStocksByOwner($owner){ //,$sortby){
		
		$mongo = $this->queryMongoMany($owner);
		$yql = $this->fetchFromYQL($mongo);
		return $this->combineYQLandMongoArrays($mongo, $yql);
	
	}