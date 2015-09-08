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
 * to read all the stocks by owner because the FUCKER WASN'T WORKING
 * RIGHT WHEN i JUST CALLED getAllStocksByOwner($owner).  So, it worked 
 * copying and pasting the code into each method that needed that functionality. 
 * Fucking annoying.  Never did figure out why it wouldn't work.  
 * 
 */

// handles querying YQL
include_once('YQL.php');//incude sends warning if fails, require is fatal.

class MongoToYQL_Adapter {
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
	

	
	
	/** get all stocks owned by a a user from the db
	 * (not from YQL)
	 * -----> alphabetized ascending by Symbol
	 *
	 * @param $owner owner of the stocks
	 * 
	 * @return $theStocksArray an array of PHP arrays of the stocks owned by that dude
	 */
	function getAllStocksByOwner($owner,$sortby){

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



		// get connection to db squared away
		$db = $this->dbconn->selectDB("test");
		$collection = $db->stocks; 
		
		/*******************************************************
		 *    build the mongo query & run it
		*******************************************************/
		$findThis = array('owner' => $owner);
		// ORIGINAL (unsorted): $cursor = $collection->find($findThis);//$findThis);
		
		$sortByThis = array('Name' => 1 );// +1 is ascending order
		// Sort on field x, ascending		//$cursor->sort(array('x' => 1));
		$cursor = $collection->find($findThis)->sort($sortByThis);
		
		/*******************************************************
		 *    build the PHP array of arrays to return
		*******************************************************/
		$theStocks = array();
		foreach ($cursor as $document) {

	
			/* Remove that stock (to be replaced later in this method).
			 * REVELATION: MongoDB embeds its ids into a special little MongoID object
			 * NOTE: just wipe out the original and replace it with a new one, rather 
			 * than mucking around 'editing' it. 
			*/
			$collection->remove( array("_id" => new MongoId( $document['_id'] )) );

			/* Get current data on that stock from YQL
			 * NOTE: This YQL class receives JSON from YQL query, but converts the JSON 
			 * into a PHP variable (a multi-dimensional array), so it's easy to weedle out 
			 * pieces you need with nice PHP operators. 
			 */
			$resultArray = $y->getQuote( $document['symbol'] );

			/* YQL gives superfluous data, so just take what we want from the JSON object,
			 * and save in a local array (need to cast it from "stdClass Object" to array.
			 */

			$updatedRecord = (array)$resultArray->{'query'}->{'results'}->{'quote'};
			/* NOTE: if one symbol, 'quote' comes back as one JSON object, but
			   if many symbols, 'quote' comes back as an array of many JSON objects
			 */

			/* Musings about my strategy here:
				Is it easier to update just current price? or clobber orig record, 
				and just add orig date, quant, fee, etc?
				OR: prolly better to only keep user's originally inputed date 
				(fee, symbol, price, etc..) because it's looking like we're going
				to be querying YQL every time the page is refreshed. 

				That may be OK for 50 or 100 stocks (querying just the quote), 
				but that cannot be the strategy for years worth of data on 
				each stock. 

				I'm disappointed with these strategies as they rely on the speed
				of querying YQL.  Yuck. 
			*/


			$updatedRecord['purchasedate'] = $document['purchasedate'];
			$updatedRecord['purchasequantity'] = $document['purchasequantity'];
			$updatedRecord['purchaseprice'] = $document['purchaseprice'];
			$updatedRecord['purchasefee'] = $document['purchasefee'];
			$updatedRecord['account'] = $document['account'];
			$updatedRecord['owner'] = $document['owner'];
		
			/*******************************************************
			 *         ADD SYMBOL (YHOO) TO THE DATABASE
			 *******************************************************/
			/* The PHP MongDB Driver accepts only PHP arrays for inserts and queries
			 * (see here: http://www.php.net/manual/en/mongo.queries.php)
			* So you need to convert your JSON to an array.
			*/
			$collection->insert($updatedRecord);

			
			/*******************************************************
			 * INSERT THE UPDATED RECORD INTO ARRAY TO BE RETURNED BY THIS METHOD.
			 *******************************************************/
			$theStocks[] = $updatedRecord;
		}
		
		//echo "<pre>";  //this proves my function works just fine. 
		//print_r($theStocks);
		//echo "</pre>";
				
		return $theStocks;
		
	}

}

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


?>
