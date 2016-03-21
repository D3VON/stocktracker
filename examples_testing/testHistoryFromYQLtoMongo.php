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
$yql = new YQL_forTesting();

$symbol = "fb";


class MongoSingleton{

    /**************************************************************************
    Data members
     **************************************************************************/
    private static $dbconn;

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct()
    {
        $dbconn = new MongoClient();
        $db = $dbconn->selectDB("test");
    }

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance()
    {
        if (null === static::$dbconn) {
            echo "it's a new Mongo connection.<br>";
            static::$dbconn = new static();
        }

        echo "it's a re-used Mongo connection.<br>";
        return static::$dbconn;
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }
}





/**Open PostgreSQL connection
 * loop through given JSON result passed as argument
 * saving each date's data in a separate query to PSQL
 */
function addNewToPostgreSQL(&$yql,$symbol){
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
/* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  TEST TEST TEST TEST TEST TEST   TEST TEST TEST TEST TEST TEST   */
//addNewToPostgreSQL($yql,$symbol);




/**Open MongoDB connection
 * loop through given JSON result passed as argument
 * saving each date's data in a separate query to Mongo
 */
function addNewToMongo(&$yql,$symbol){

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
        $index = $json['query']['count'] - 1;
        echo "$index<br>";

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

    echo "<pre>"; var_dump($theHistory); echo "</pre>";

    /* The PHP MongoDB Driver accepts only PHP arrays for inserts and queries
     * (see here: http://www.php.net/manual/en/mongo.queries.php)
     */
    $collection->insert($theHistory);

}
/* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  TEST TEST TEST TEST TEST TEST   TEST TEST TEST TEST TEST TEST   */
//addNewToMongo($yql,$symbol);
// ---------------------to clean up after that test if necessary-------------
//notInHistory($symbol);
//removeFromMongo($symbol);
//notInHistory($symbol);



/** Mongo Version
 * */
function notInHistory($symbol){

    $dbconn = new MongoClient();
    $db = $dbconn->selectDB("test");
    $collection = $db->history;

    $findThis = array('symbol' => $symbol);
    $doc = $collection->findOne($findThis);

    if(!empty($doc) ){
        echo "Data Already Exists.<br>";
    } else {
        echo "Data Doesn't Exist.<br>";
    }
///////////////////////////////////////////////////////////////////////////////////////////should return TRUE or FALSE

}

/* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  TEST TEST TEST TEST TEST TEST   TEST TEST TEST TEST TEST TEST   */
//notInHistory("ibm");
//notInHistory("lng");



function getLastDate($symbol){

    $dbconn = new MongoClient();
    $db = $dbconn->selectDB("test");
    $collection = $db->history;


    // SELECT a,b FROM users WHERE age=33
    // $db->users->find(array("age" => 33), array("a" => 1, "b" => 1));

    $findThis = array('symbol' => $symbol);
    $returnThis = array("lastday" => 1);

    $doc = $collection->findOne($findThis,$returnThis);
    // INTERESTING: find returns a cursor, findOne returns a nice array.
    // So, you can't manipulate a cursor directly, but you can an array.

    if(empty($doc)) {
        echo "That stock doesn't exist";
    } else {
//        echo "Here's the var-dump: <br>";
//        echo "<pre>"; var_dump($doc["lastday"]["date"]); echo "</pre>";
//        echo "last date is: " . $doc["lastday"]["date"];
        return $doc["lastday"]["date"];
    }

}
/* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  TEST TEST TEST TEST TEST TEST   TEST TEST TEST TEST TEST TEST   */
//getLastDate("lng");


function needsHistoricUpdate($symbol){

    $lastdate = getLastDate($symbol);
    // $lastdate = date('Y-m-d'); // for testing purps

    if(strtotime($lastdate) < strtotime(date('Y-m-d'))){
        echo "last date: $lastdate is ". strtotime($lastdate). "<br>";
        echo "curent date: " . date('Y-m-d') . " is ". strtotime(date('Y-m-d')) . "<br>";
        echo "Last date was in the past.<br>";
    }

    echo "today is the " . date('N') . " day of the week.<br>";

    echo "the hour now is " . date("H") . "<br>";
    echo "the minute now is " . date("i") . "<br>";


    //if(date('N')>5 || (date("H")>16 && date("i">30)))
    $date1 = new DateTime($lastdate);
    $date2 = new DateTime(date('Y-m-d'));
    $diff = $date2->diff($date1)->format("%a");
    echo "$diff is the difference in days<br>";



}
/* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  TEST TEST TEST TEST TEST TEST   TEST TEST TEST TEST TEST TEST   */
//needsHistoricUpdate('fb');



/* add a fake day, just to show the algorithm of adding a small bit to a giant array
   --it will query existing,
   --find last day
   --make up the day beyond that with fake data
   --append to array
   then print it all out.
*/
function addFakeDay($symbol){

    $dbconn = new MongoClient();
    $db = $dbconn->selectDB("test");
    $collection = $db->history;

    // grab existing document
    $findThis = array('symbol' => $symbol);

    // works to destroy the last one, but won't show you what it was. Bitches!
    // $poplast = array('$pop' => array('day' => 1)); // -1 is first elem. +1 is last elem.
    // $doc = $collection->update($findThis,$poplast);

    echo "<pre>"; var_dump($doc); echo "</pre>";

/*
    --make up the day beyond that with fake data
    --append to array
        then print it all out.
*/

}
/* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  TEST TEST TEST TEST TEST TEST   TEST TEST TEST TEST TEST TEST   */
//addFakeDay($symbol);


function removeFromMongo($symbol){
    $dbconn = new MongoClient();
    $db = $dbconn->selectDB("test");
    $collection = $db->history;

    $collection->remove( array("symbol" => $symbol) );
}
/* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  TEST TEST TEST TEST TEST TEST   TEST TEST TEST TEST TEST TEST   */
//notInHistory($symbol);
//removeFromMongo($symbol);
//notInHistory($symbol);








/**Open MongoDB connection
 * loop through given JSON result passed as argument
 * saving each date's data in a separate query to Mongo
 */
function bringHistoryCurrentInMongo(&$yql,$symbol){
//THIS IS A COPY OF addToMongo() THAT HASN'T BEEN MODIFIED YET.
//THIS IS A COPY OF addToMongo() THAT HASN'T BEEN MODIFIED YET.
//THIS IS A COPY OF addToMongo() THAT HASN'T BEEN MODIFIED YET.
//THIS IS A COPY OF addToMongo() THAT HASN'T BEEN MODIFIED YET.
//THIS IS A COPY OF addToMongo() THAT HASN'T BEEN MODIFIED YET.
//THIS IS A COPY OF addToMongo() THAT HASN'T BEEN MODIFIED YET.
//THIS IS A COPY OF addToMongo() THAT HASN'T BEEN MODIFIED YET.
//THIS IS A COPY OF addToMongo() THAT HASN'T BEEN MODIFIED YET.
//THIS IS A COPY OF addToMongo() THAT HASN'T BEEN MODIFIED YET.
//THIS IS A COPY OF addToMongo() THAT HASN'T BEEN MODIFIED YET.
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
        $index = $json['query']['count'] - 1;
        echo "$index<br>";

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

    echo "<pre>"; var_dump($theHistory); echo "</pre>";

    /* The PHP MongoDB Driver accepts only PHP arrays for inserts and queries
     * (see here: http://www.php.net/manual/en/mongo.queries.php)
     */
    $collection->insert($theHistory);

}
/* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  TEST TEST TEST TEST TEST TEST   TEST TEST TEST TEST TEST TEST   */
//bringHistoryCurrentInMongo($yql,$symbol);
// ---------------------to clean up after that test if necessary-------------
//notInHistory($symbol);
//removeFromMongo($symbol);
//notInHistory($symbol);











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
