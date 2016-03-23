<?php // historyCron.php
/**
 * This is to be run as a cron job.  It queries the 'history' collection
 * in the Mongo database.  (Currently just the test data store.)
 * After it gets the set of symbols from 'history', it querys YQL
 * for the quotes.  The cron job runs after the market closes
 * (say at 5 pm, 1/2 an hour after closing--to be safe because right now
 * I don't have an account with YQL to get (nearly) real time quotes, and so,
 * quotes are officially delayed by 15 minutes; I don't trust that, so I am
 * adding another 15 minutes to get the certain last values of the day).
 * This script then loops through those quotes and appends the end-of-day
 * values to the 'history' collection of stocks.
 *
 * Note: if history is queried during the trading day, it will return
 * data up to and including the previous trading day.  When graphing
 * a stock's history, another query will need to be run for the current price.
 *
 * Created by PhpStorm.
 * User: devonmcb
 * Date: 3/20/16
 * Time: 11:20 PM
 */

require_once('YQL.php');
//require_once("../global.php");

$yql = new YQL();

$dbconn = new MongoClient(); // no credentials needed b.c. the database is running on the same machine as this script, and this script is running as the same user.
$db = $dbconn->selectDB("test");
$collection = $db->history;


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

    global $yql;

    // NOTE: This YQL class receives JSON from YQL query, but converts the JSON
    // into a PHP variable (a multi-dimensional array), so it's easy to weedle out
    // pieces you need with nice PHP operators.
    $resultArray = $yql->getQuote( $symbolsString );
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



function getSymbolsFromHistory(){
    global $collection;
    $result = array();

    foreach ($collection->find(array(),array("symbol" => 1)) as $document) {
        $result[] = $document["symbol"];
    }
    return $result;
}
                                        /* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  TEST TEST TEST TEST TEST TEST   TEST TEST TEST TEST TEST TEST   */
                                        //$stocklist = getSymbolsFromHistory();
                                        //echo "<pre>"; var_dump($stocklist); echo "</pre>";


function getQuotesFromYQL($stocklist){

    global $yql;

    // build the string of stocks to feed to the YQL query function
    $quotecommaquote = "%22,%22";
    $thelist = array_shift($stocklist);
    foreach($stocklist as $s){
        $thelist .= $quotecommaquote . $s;
    }

    return fetchManyFromYQL($thelist);
}
                                        /* TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST  TEST TEST TEST TEST TEST TEST   TEST TEST TEST TEST TEST TEST   */
                                        //$stocklist = getSymbolsFromHistory();
                                        //$quotes = getQuotesFromYQL($stocklist);
                                        //echo "<pre>"; var_dump($quotes); echo "</pre>";

function dailyHistoryUpdate(){
    global $collection;

    $stocklist = getSymbolsFromHistory();
    $quotes = getQuotesFromYQL($stocklist);

    foreach($quotes as $q){
        $tempDay = array();
        $tempDay["date"] = date('Y-m-d');//YQL quote does not give date
                                        /********** testing: */
                                        //$tempDay["date"] = '2014-43-TESTING-TESTING';
        $tempDay["volume"] = $q["AverageDailyVolume"];
        $tempDay["closingprice"] = $q["LastTradePriceOnly"];

        $collection->update(
            array("symbol" => $q["symbol"]),
            array('$push' => array("day" => $tempDay) )  // adds the daily quote array to the end of the 'day' array
        );
        $collection->update(
            array("symbol" => $q["symbol"]),
            array('$set' => array("lastday" => $tempDay) ) // clobbers existing 'lastday' array
        );
    }
}
dailyHistoryUpdate();


















