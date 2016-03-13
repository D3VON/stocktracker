<?php
/**
 * Created by PhpStorm.
 * User: devonmcb
 * Date: 3/9/16
 * Time: 12:35 AM
 */


// .. hardcoded for examples_testing directory
require_once('../YQL_forTesting.php');
require_once("../global.php");

$symbol = "lng";

$yql = new YQL_forTesting();


/**Open PostgreSQL connection
 * loop through given JSON result passed as argument
 * saving each date's data in a separate query to PSQL
 */
function doPostgreSQL(&$yql,$symbol){
    //a very hard-coded db connection to PostgreSQL
    $dbconn = pg_connect("host="	 .DB_HOST
        ." dbname="	 .DATABASE
        ." user="	 .DB_USERNAME
        ." password=".DB_USERPASS
    )
    or die('connection failed');

    // NOTE: This YQL class receives JSON from YQL query, but converts the JSON
    // into a PHP variable (a multi-dimensional array), so it's easy to weedle out
    // pieces you need with nice PHP operators.
    $resultArray = $yql->populateHistoricalData($symbol);
    /* WATCH OUT!  This was formed by querying YQL several times (once for each year), so
       it contains a separate multi-dimensional array for each year. You'll have to
       foreach loop through each year to weedle out the contiguous info you want.
     */

    //echo "<pre>"; var_dump($resultArray); echo "</pre>";

    /* This damned thing takes like 60 seconds to run. */
    // about 9 JSON elements in that array (representing whole years worth of quotes)
    /* PostgreSQL version of capturing many years worth of a stock's price */
    foreach($resultArray as $json){

        //testing: just print count of days in that year to show that something happened (if not showing data for each day)
        $index = $json['query']['count'];
        echo "$index<br>";

        //foreach ($json->query->results->quote as $Q){
        for($index -=1; $index >= 0; $index--){
            //each ($json['query']['results']['quote'] as $Q){

            $query = "INSERT INTO history VALUES";
            $query .= "('$symbol','";
            $query .= $json['query']['results']['quote'][$index]['Date'] . "',";
            $query .= $json['query']['results']['quote'][$index]['Close'] . ",";
            $query .= $json['query']['results']['quote'][$index]['Volume'] . ")";
            //$query .= "('$symbol','" . $Q->Date . "'," . $Q->Close . "," . $Q->Volume . ")";

            $result = pg_query($query);
            if(!$result){
                echo 'Historicalquotes query failed: ' . pg_last_error();
            }
            //if(isset($json->query->results->quote))
        }
    }

}
/* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  */
//doPostgreSQL($yql,$symbol);




/**Open MongoDB connection
 * loop through given JSON result passed as argument
 * saving each date's data in a separate query to Mongo
 */
function doMongo(&$yql,$symbol){

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
    // about 9 JSON elements in that array (representing whole years worth of quotes)
    /* MongoDB version of capturing many years worth of a stock's price */
    foreach($resultArray as $json){

        //testing: just print count of days in that year to show that something happened (if not showing data for each day)
        $index = $json['query']['count'];
        echo "$index<br>";

        //foreach ($json->query->results->quote as $Q){
        for($index -=1; $index >= 0; $index--){

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
    }

    echo "<pre>"; var_dump($theHistory); echo "</pre>";

    /* The PHP MongoDB Driver accepts only PHP arrays for inserts and queries
     * (see here: http://www.php.net/manual/en/mongo.queries.php)
     */
    //$collection->insert($theHistory);

}
/* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  */
//doMongo($yql,$symbol);




/** Mongo Version
 * */
function notInHistory($symbol){

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

    $findThis = array('symbol' => $symbol);
    $doc = $collection->findOne($findThis);

    if(!empty($doc) ){
        echo "Data Already Exists.";
    } else {
        echo "Data Doesn't Exist.";
    }


}

//notInHistory("ibm");
notInHistory("lng");

/*

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
