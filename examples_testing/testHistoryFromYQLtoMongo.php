<?php
/**
 * Created by PhpStorm.
 * User: devonmcb
 * Date: 3/9/16
 * Time: 12:35 AM
 */


// .. hardcoded for examples_testing directory
require_once('../YQL.php');

$symbol = "YHOO";

$yql = new YQL();

// NOTE: This YQL class receives JSON from YQL query, but converts the JSON
// into a PHP variable (a multi-dimensional array), so it's easy to weedle out
// pieces you need with nice PHP operators.
$resultArray = $yql->populateHistoricalData($symbol);
/* WATCH OUT!  This was formed by querying YQL several times (once for each year), so
   it contains a separate multi-dimensional array for each year. You'll have to
   foreach loop through each year to weedle out the contiguous info you want.
 */




echo "<pre>"; var_dump($resultArray); echo "</pre>";
//-------------------------------------------------------------------------------good up to here.  Got the history.


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

/* this section: add to mongodb
//$this->dbconn = new MongoClient("mongodb://${username}:${password}@localhost/myDatabase");
$dbconn = new MongoClient();
$db = $dbconn->selectDB("test");
$collection = $db->stocks;

// add a record
$document = array( "title" => "Calvin and Hobbes", "author" => "Bill Watterson" );
$collection->insert($document);

// add another record, with a different "shape"
$document = array( "title" => "XKCD", "online" => true );
$collection->insert($document);

// find everything in the collection
$cursor = $collection->find();

// iterate through the results
foreach ($cursor as $document) {
    echo $document["title"] . "\n";
}


*/
