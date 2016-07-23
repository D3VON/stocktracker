<?php // testhistoryfunctions.php
/**
 * Created by PhpStorm.
 * User: devonmcb
 * Date: 7/16/16
 * Time: 3:38 PM
 *
 *
 *
 *
 *
 */



// handles querying YQL
include_once('../YQL.php');//incude sends warning if fails, require is fatal.
include_once('../MongoToYQL_Adapter.php');

// to test to see if the object instantiation works and a method can be called.
function dummy(){ return "<br><br>Is this thing on? Testing, testing.<br>"; }







// set up YQL connection to get current stock info
$y = new YQL;
$db = new MongoToYQL_Adapter;

echo dummy();


$symbol = "LNG";
//foreach($y->populateHistoricalData($symbol) as $woof){
//    print_r($woof);
//
//}

// In MongoToYQLAdapter.php, calls YQL's populateHistoricalData()
$db->populateOneHistoryFromYQL($symbol);



