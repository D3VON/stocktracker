<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>example_storeJSONtoMongo</title>
  <!--  link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css"> -->
  <!--  script src="//code.jquery.com/jquery-1.10.2.js"></script> -->
  <!--  script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script> -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
  <!--  link rel="stylesheet" href="/resources/demos/style.css"> -->
  <script>
  $(function() {
    $( "#datepicker" ).datepicker();
  });
  </script>
</head>
<body>
 
<p>Date: <input type="text" id="datepicker"></p>
 
 
</body>
</html>



<?php  // example_storeJSONtoMongo.php

/****************************************************************************
 * This example does a YQL query for stock symbol YHOO,
 * -> adds the entire result to MongoDB's 'test' database 
 *    (NOTE: I'm here assuming a totally empty database, so if it isn't, 
 *    that'll obviously make some of these simple queries return a lot.), 
 * -> shows the database contents, 
 * 
 * -> modifies the document, 
 * -> shows the contents again, 
 * 
 * -> removes that record
 * -> shows the database contents (should be empty)
 * 
 * -> does YQL query, removes some fields, adds new ones, saves to Mongo
 * -> shows the contents again, 
 * 
 * -> removes everything from mongo, 
 * -> shows the contents again (db should be empty).
 */

// handles querying YQL
include_once('YQL.php');

/*************************************************************/
// Set up a Query YQL for quote, and run the YQL query
/*************************************************************/
$y = new YQL;
$symbol = "YHOO";


/****************************************************************************
 *  SET UP DATABASE CONNECTION FOR USE THROUGHOUT THE REST OF THIS SCRIPT
*****************************************************************************/
/* NOTE: YQL class receives JSON from YQL query, but converts the JSON 
 * into a PHP variable (a multi-dimensional array), so it's easy to weedle out 
 * pieces you need with nice PHP operators. 
 */
$resultArray = $y->getQuote($symbol);
$mongo = new MongoClient();// when I was having problems, try/catch was uselessly silent, so screw it!
$db = $mongo->selectDB("test");
$collection = $db->stocks; //$collection = new MongoCollection($db, "stocks");


/*******************************************************
 *         ADD SYMBOL (YHOO) TO THE DATABASE
*******************************************************/
/* The PHP MongDB Driver accepts only PHP arrays for inserts and queries 
 * (see here: http://www.php.net/manual/en/mongo.queries.php)
 * So you need to convert your JSON to an array.
 */
$collection->insert($resultArray);

/*******************************************************
 *         SHOW ALL RECORDS IN THE DATABASE
*******************************************************/
$cursor = $collection->find();
foreach ($cursor as $doc) {
	echo "<BR>MEOW, MEOW, MEOW, MEOW, MEOW, MEOW, MEOW, MEOW, MEOW, MEOW, MEOW, MEOW, <BR>";
	
	echo "<pre>";
	// can also nicely reference deeply nested fields thusly:
	print_r($doc['query']['results']['quote']);
	/* 	NOTE: if there's a document in that collection with some structure 
		not like that, it'll count in any tally, but it will only produce
		blank / empty results 
	*/
	echo "</pre>";
}

/*******************************************************
 *  MODIFY A FIELD IN VERY NESTED JSON IN THE DATABASE
*******************************************************/

/* Awesome example of how to modify a field in a record.  
 * There is a DANGER that you could clobber the entire record and then 
 * only be left with the new data and nothing else (wiped away forever). 
 * Apparently, the Mongo '$set' operator handles the fine-grained modification
 * we desire.  
 */
// selector based on a nested field (dot operator works really well here! --so easy!)
$newdata = array('$set' => array("query.results.quote.AverageDailyVolume" => 11111111111111));
// selector based on a nested field (dot operator works really well here! --so easy!)
$collection->update(array("query.results.quote.Symbol" => "YHOO"), $newdata, array('multiple' => true));
// NOTE TO NOOBS: here, I'm trying to update all docs that match the selector, so I need the 'multi' option. 
//$collection->update(array("query.results.quote.Symbol" => "YHOO"), $newdata, array('multiple' => true));


/*******************************************************
 *         SHOW ALL RECORDS IN THE DATABASE
*******************************************************/
$cursor = $collection->find();
foreach ($cursor as $doc) {
	echo "<BR>WOOF, WOOF, WOOF, WOOF, WOOF, WOOF, WOOF, WOOF, WOOF, WOOF, WOOF, WOOF, WOOF<BR>";
	
	echo "<pre>";
	// can also nicely reference deeply nested fields thusly:
	print_r($doc['query']['results']['quote']);
	/* 	NOTE: if there's a document in that collection with some structure 
		not like that, it'll count in any tally, but it will only produce
		blank / empty results 
	*/
	echo "</pre>";
}




/*******************************************************
 *         REMOVE ALL MATCHING SYMBOLS (YHOO)
*******************************************************/
echo "--------- removed all records matching 'YHOO'------------<BR>";
echo "------- db currently empty ----------<br>";
//$collection->remove( array("query.results.quote.Symbol" => "YHOO") );

/*******************************************************
 *         SHOW ALL RECORDS IN THE DATABASE
*******************************************************/
$cursor = $collection->find();
foreach ($cursor as $doc) {
	echo "<BR>THERE SHOULD BE NOTHING HERE, IF YOU'RE READING THIS SOMETHING WENT WRONG. <BR>";

	echo "<pre>";
	// can also nicely reference deeply nested fields thusly:
	//print_r($doc['query']['results']['quote']); // (made the structure simpler.
	print_r($doc);
	/* 	NOTE: if there's a document in that collection with some structure
		not corresponding to a specific reference like $doc['query']['results']['quote'],
	it'll count in any agrigate function like tally,
	but it will only produce blank / empty results
	*/
	echo "</pre>";
}



/*******************************************************
 *  ADD FIELDS IN VERY NESTED JSON IN THE DATABASE
*******************************************************/
echo "Querying YQL, removing some fields, adding others, storing to Mongo<br>";
/* Using same $resultArray from above, add more fields to it
 * I realized it doesn't matter where they are, as long as
 * I know where they are for retrieval, so, just append
 * any additional fields that I want to the end.
 * */
$date = "7/8/69";
$quantity = 156;
$price = 127.340;
$account = "Fidelity IRA";
$fee = 7.00;
$owner = "me";                 // Here I'm simulating user input


/* YQL gives superfluous data, so just take what we want from the JSON object,
 * and save in a local array (need to cast it from "stdClass Object" to array.
 */
$thePurchase = (array)$resultArray->{'query'}->{'results'}->{'quote'};
$thePurchase['purchasedate'] = $date;
$thePurchase['purchasequantity'] = $quantity;
$thePurchase['purchaseprice'] = $price;
$thePurchase['account'] = $account;
$thePurchase['purchasefee'] = $fee;
$thePurchase['owner'] = $owner;

/* The PHP MongDB Driver accepts only PHP arrays for inserts and queries
 * (see here: http://www.php.net/manual/en/mongo.queries.php)
 * So you need to convert your JSON to an array.
 */
$collection->insert($thePurchase);

/*******************************************************
 *         SHOW ALL RECORDS IN THE DATABASE
*******************************************************/
$cursor = $collection->find();
foreach ($cursor as $doc) {
	echo "<BR>BARK, BARK, BARK, BARK, BARK, BARK, BARK, BARK, BARK, BARK, BARK, BARK, BARK<BR>";

	echo "<pre>";
	print_r($doc);
	echo "</pre>";
}


/*******************************************************
 *         REMOVE ALL MATCHING SYMBOLS (YHOO)
 *******************************************************/
echo "--------- removing all records matching 'YHOO'------------<BR>";
echo "------- this should not show anything after here----------";
//$collection->remove( array("Symbol" => "YHOO") );

/*******************************************************
 *         SHOW ALL RECORDS IN THE DATABASE
*******************************************************/
$cursor = $collection->find();
foreach ($cursor as $doc) {
	echo "<BR>THERE SHOULD BE NOTHING HERE, IF YOU'RE READING THIS SOMETHING WENT WRONG. <BR>";

	echo "<pre>";
	// can also nicely reference deeply nested fields thusly:
	//print_r($doc['query']['results']['quote']); // (made the structure simpler. 
	print_r($doc);
	/* 	NOTE: if there's a document in that collection with some structure
		not corresponding to a specific reference like $doc['query']['results']['quote'], 
		it'll count in any agrigate function like tally, 
		but it will only produce blank / empty results
	*/
	echo "</pre>";
}

/* ************************************************************************************
 *  ************************************************************************************
 *   ************************************************************************************
 *    ************************************************************************************
 *     ************************************************************************************
 *      ************************************************************************************
 *      trying to query the db for an owner's stocks, put them into an array for eventual passsing somewheres
 * */

$owner = "me";
/*******************************************************
 *    build the mongo query & run it
*******************************************************/
$findThis = array('owner' => $owner);
$cursor = $collection->find($findThis);

/*******************************************************
 *    build the PHP array of arrays to return
*******************************************************/
$theStocks = array();
foreach ($cursor as $document) {
	$theStocks[] = $document;
}

echo "********************************************************************************<br>";
echo "********************************************************************************<br>";
echo "********************************************************************************<br>";
echo "********************************************************************************<br>";
echo "********************************************************************************<br>";
echo "********************************************************************************<br>";
echo "<pre>";
// can also nicely reference deeply nested fields thusly:
print_r($theStocks);
/* 	NOTE: if there's a document in that collection with some structure
 not like that, it'll count in any tally, but it will only produce
blank / empty results
*/
echo "</pre>";





?>


