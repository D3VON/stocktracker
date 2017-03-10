<?php  // TestMongoToYQLAdapter.php


    /*
     * 	functions to poke and prod my datastores in the 'test' database:
     *      history -- the one used by stocktracker, quite simple, updated by cron
     *      histories -- more complete, not used, not updated by cron
     *
     * FUNCTIONS:
     *
     * AreArraysPassedByValOrRef(&$arr)
     * fetchOneFromYQL($symbol)
     * fetchFromYQL(&$mongoArray)
     * fetchManyFromYQL($symbols)
     * queryMongoMany($owner)
     * combineYQLandMongoArrays($mongo, $yql)
     * function getAllStocksByOwner($owner,$sortby)
     *
     * ....THESE SHOULD BE DONE BY JAVASCRIPT IN THE BROWSER.
     * sortByAccountAsc($a,$b)
     * sortByAccountDesc($a,$b)
     * sortBySymbolAsc($a,$b)
     * sortBySymbolDesc($a,$b)
     * sortByPurchaseDateAsc($a, $b)
     * sortByPurchaseDateDesc($a, $b)
     * sortByNameAsc($a,$b)
     * sortByNameDesc($a,$b)
     * sortByPercentChangeTodayAsc($a,$b)
     * sortByPercentChangeTodayDesc($a,$b)
     * sortByTotalChangePercentAsc($a,$b)
     * sortByTotalChangePercentDesc($a,$b)
     * sortByPurchaseTotalAsc($a,$b)
     * sortByPurchaseTotalDesc($a,$b)
    */

error_reporting(E_ALL);

    // handles querying YQL
include_once('YQL.php');//incude sends warning if fails, require is fatal.


// this was my attempt at an enum
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
     * @param String $owner the owner of the stocks
     *
     *
     * @return array $theStocksArray a complete array the stocks owned by the given user
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
    function combineYQLandMongoArrays($mongo, $yql){
        $newArray = array();

        $len = count($mongo);
        if($len != count($yql)) {
            echo "Error retrieving data.<br>";
        }

        $s = array(); // the stock


        /*notetoself: calculated fields are calculated in StocksTable.php
         * That cannot continue.  I'm calculating them here as I receive them
         * from the db instead.
           // never used: $dollarchange = $s['LastTradePriceOnly'] - $s['purchaseprice'];
         */
        for ($i = 0; $i < $len; $i++){ // can't use foreach b.c. need to refer back to orig. element
            $s["symbol"] 				= $yql[$i]["symbol"];
            $s["AverageDailyVolume"] 	= $yql[$i]["AverageDailyVolume"];
            $s["Change"] 				= $yql[$i]["Change"];
            $s["DaysLow"] 				= $yql[$i]["DaysLow"];
            $s["DaysHigh"] 				= $yql[$i]["DaysHigh"];
            $s["YearLow"] 				= $yql[$i]["YearLow"];
            $s["YearHigh"] 				= $yql[$i]["YearHigh"];
            $s["MarketCapitalization"] 	= $yql[$i]["MarketCapitalization"];
            $s["LastTradePriceOnly"] 	= $yql[$i]["LastTradePriceOnly"];
            if ($s['LastTradePriceOnly'] == 0){ $s["percentchangetoday"] = "no value"; }
            else { $s["percentchangetoday"]	= $yql[$i]["Change"] / $s['LastTradePriceOnly'] * 100; }
            $s["DaysRange"] 			= $yql[$i]["DaysRange"];
            $s["Name"] 					= $yql[$i]["Name"];
            $s["Volume"] 				= $yql[$i]["Volume"];
            $s["StockExchange"] 		= $yql[$i]["StockExchange"];
            $s["_id"] 					= $mongo[$i]["_id"];
            $s["purchasedate"] 			= $mongo[$i]["purchasedate"];
            $s["purchasequantity"] 		= $mongo[$i]["purchasequantity"];
            $s["purchaseprice"] 		= $mongo[$i]["purchaseprice"];
            $s["purchasefee"] 			= $mongo[$i]["purchasefee"];
            $s["purchasetotal"] 		= $mongo[$i]["purchasefee"] + ($mongo[$i]["purchaseprice"] * $mongo[$i]["purchasequantity"]);
            $s["account"] 				= $mongo[$i]["account"];
            $s["owner"] 				= $mongo[$i]["owner"];
            $s["totalCurrentValue"] 	= $s["purchasequantity"] * $s['LastTradePriceOnly'] - $s['purchasefee'];
            $s["totalChangeDollar"] 	= $s["totalCurrentValue"] - $s["purchasetotal"];
            $s["totalChangePercent"]	= $s["totalChangeDollar"] / $s["purchasetotal"] * 100;

            $newArray[$i] = $s;
        }

        return $newArray;

    }

    function getAllStocksByOwner($owner,$sortby){
        /*
        TO-DO:
        DONE $a = queryMongoMany($owner); <----modify to accept $owner argument
        DONE $d = fetchFromYQL($a); // clearly, $d will be compatible with $a
        DONE $e = combineYQLandMongoArrays($a, $d);

        Put into Adapter class to see if I can get that working again (without sorting yet)

        - $theStocks = usort($e, sortfunction); // make a sort function
        - --------hopefully sort functions will be very similar to eachother.


        return $theStocks;
        */
    }

    /******************************************************************************
     * ****************************************************************************
     * ****************************************************************************
     * Following here are all the functions to sort by column
     * ****************************************************************************
     * NOTE: I don't know how to avoid this repetition of code, each pair (ascending
     * and descending) only different by which array element they are sorted by,
     * because PHP's usort() takes one function with a unique name (represending
     * exactly what needs to be sorted and how) with a very specific signature that
     * lacks the ability to pass which element I want to search by. So I'm forced
     * to make essentially the same function so many times with that slight variation.
     * ****************************************************************************
     * ****************************************************************************
     */


    /** for use by usort thusly: usort($array,"sortByAccount");
     *	$array is an array of any sort, even multi-dimensional
     *
     * @param String $a an element in $array -- usort handles indexing
     * @param String $b an element in $array -- usort handles indexing
     *
     * @return int -1, 0, or 1 -- for use by usort
     *
     */
    function sortByAccountAsc($a,$b)
    {
        if ($a['account']==$b['account']) return 0;
        return ($a['account']<$b['account'])?-1:1;
    }

    //similar to above
    function sortByAccountDesc($a,$b)
    {
        if ($a['account']==$b['account']) return 0;
        return ($a['account']>$b['account'])?-1:1;
    }

    //similar to above
    function sortBySymbolAsc($a,$b)
    {
        if ($a['symbol']==$b['symbol']) return 0;
        return ($a['symbol']<$b['symbol'])?-1:1;
    }

    //similar to above
    function sortBySymbolDesc($a,$b)
    {
        if ($a['symbol']==$b['symbol']) return 0;
        return ($a['symbol']>$b['symbol'])?-1:1;
    }

    //similar to above, but need a special trick for the date
    function sortByPurchaseDateAsc($a, $b) {
        $aval = strtotime($a['purchasedate']);
        $bval = strtotime($b['purchasedate']);
        if ($aval == $bval) {
            return 0;
        }
        return $aval < $bval ? -1 : 1;
    }
    //similar to above, but need a special trick for the date
    function sortByPurchaseDateDesc($a, $b) {
        $aval = strtotime($a['purchasedate']);
        $bval = strtotime($b['purchasedate']);
        if ($aval == $bval) {
            return 0;
        }
        return $aval > $bval ? -1 : 1;
    }

    //similar to above
    function sortByNameAsc($a,$b)
    {
        if ($a['Name']==$b['Name']) return 0;
        return ($a['Name']<$b['Name'])?-1:1;
    }

    //similar to above
    function sortByNameDesc($a,$b)
    {
        if ($a['Name']==$b['Name']) return 0;
        return ($a['Name']>$b['Name'])?-1:1;
    }

    //similar to above
    function sortByPercentChangeTodayAsc($a,$b)
    {
        if ($a['percentchangetoday']==$b['percentchangetoday']) return 0;
        return ($a['percentchangetoday']<$b['percentchangetoday'])?-1:1;
    }

    //similar to above
    function sortByPercentChangeTodayDesc($a,$b)
    {
        if ($a['percentchangetoday']==$b['percentchangetoday']) return 0;
        return ($a['percentchangetoday']>$b['percentchangetoday'])?-1:1;
    }

    //similar to above
    function sortByTotalChangePercentAsc($a,$b)
    {
        if ($a['totalChangePercent']==$b['totalChangePercent']) return 0;
        return ($a['totalChangePercent']<$b['totalChangePercent'])?-1:1;
    }

    //similar to above
    function sortByTotalChangePercentDesc($a,$b)
    {
        if ($a['totalChangePercent']==$b['totalChangePercent']) return 0;
        return ($a['totalChangePercent']>$b['totalChangePercent'])?-1:1;
    }

    //similar to above
    function sortByPurchaseTotalAsc($a,$b)
    {
        if ($a['purchasetotal']==$b['purchasetotal']) return 0;
        return ($a['purchasetotal']<$b['purchasetotal'])?-1:1;
    }

    //similar to above
    function sortByPurchaseTotalDesc($a,$b)
    {
        if ($a['purchasetotal']==$b['purchasetotal']) return 0;
        return ($a['purchasetotal']>$b['purchasetotal'])?-1:1;
    }


    /******************************************************************************
     * ****************************************************************************
     * ****************************************************************************
     * The driver follows here to test the functions above.
     * ****************************************************************************
     * ****************************************************************************
     * ****************************************************************************
     */

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

    /*
    usort($e,"sortByAccount");
    echo "Sorted by Account: <br>";
    echo "<pre>"; print_r($e); echo "</pre><br>-------------------------------<br>";

    usort($e,"sortBySymbolAsc");
    echo "Sorted by symbol Ascending: <br>";
    echo "<pre>"; print_r($e); echo "</pre><br>-------------------------------<br>";


    usort($e,"sortByPurchaseDateDesc");
    echo "Sorted by Date descending: <br>";
    echo "<pre>"; print_r($e); echo "</pre><br>-------------------------------<br>";


    usort($e,"sortByPurchaseDateAsc");
    echo "Sorted by Date ascending: <br>";
    echo "<pre>"; print_r($e); echo "</pre><br>-------------------------------<br>";
    */


usort($e,"sortByNameDesc");
echo "Name Desc: <br>";
foreach($e as $it){ echo $it['Name'] . "<br>";}
echo "-------------------------------------<br>";

usort($e,"sortByNameAsc");
echo "Name Ascend: <br>";
foreach($e as $it){ echo $it['Name'] . "<br>";}
echo "-------------------------------------<br>";


usort($e,"sortByPercentChangeTodayAsc");
echo "% change today Desc: <br>";
foreach($e as $it){
    echo $it['percentchangetoday'] . "<br>";
}
echo "-------------------------------------<br>";

usort($e,"sortByPercentChangeTodayDesc");
echo "% change today Ascend: <br>";
foreach($e as $it){
    echo $it['percentchangetoday'] . "<br>";
}
echo "-------------------------------------<br>";

// next

usort($e,"sortByTotalChangePercentAsc");
echo "% change total Ascend: <br>";
foreach($e as $it){
    echo $it['totalChangePercent'] . "<br>";
}
echo "-------------------------------------<br>";

usort($e,"sortByTotalChangePercentDesc");
echo "% change total Desc : <br>";
foreach($e as $it){
    echo $it['totalChangePercent'] . "<br>";
}
echo "-------------------------------------<br>";

// next

usort($e,"sortByPurchaseTotalAsc");
echo "purch total Ascend: <br>";
foreach($e as $it){
    echo $it['purchasetotal'] . "<br>";
}
echo "-------------------------------------<br>";

usort($e,"sortByPurchaseTotalDesc");
echo "purch total Desc : <br>";
foreach($e as $it){
    echo $it['purchasetotal'] . "<br>";
}
echo "-------------------------------------<br>";


/**************************DO TESTING ON THESE**********************

//similar to above
function sortByPercentChangeTodayAsc($a,$b)
{
if ($a['percentchangetoday']==$b['percentchangetoday']) return 0;
return ($a['percentchangetoday']<$b['percentchangetoday'])?-1:1;
}

//similar to above
function sortByPercentChangeTodayDesc($a,$b)
{
if ($a['percentchangetoday']==$b['percentchangetoday']) return 0;
return ($a['percentchangetoday']>$b['percentchangetoday'])?-1:1;
}

//similar to above
function sortByTotalChangePercentAsc($a,$b)
{
if ($a['totalChangePercent']==$b['totalChangePercent']) return 0;
return ($a['totalChangePercent']<$b['totalChangePercent'])?-1:1;
}

//similar to above
function sortByTotalChangePercentDesc($a,$b)
{
if ($a['totalChangePercent']==$b['totalChangePercent']) return 0;
return ($a['totalChangePercent']>$b['totalChangePercent'])?-1:1;
}

//similar to above
function sortByPurchaseTotalAsc($a,$b)
{
if ($a['purchasetotal']==$b['purchasetotal']) return 0;
return ($a['purchasetotal']<$b['purchasetotal'])?-1:1;
}

//similar to above
function sortByPurchaseTotalDesc($a,$b)
{
if ($a['purchasetotal']==$b['purchasetotal']) return 0;
return ($a['purchasetotal']>$b['purchasetotal'])?-1:1;
}




 */





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

