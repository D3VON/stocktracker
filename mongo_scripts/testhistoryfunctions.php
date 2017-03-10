<?php // testhistoryfunctions.php
/**
 * Created by PhpStorm.
 * User: devonmcb
 * Date: 7/16/16
 * Date: 3/8/17 --functions to 'fix' history datastore after cron failed to update it.
 *
 *   Comment out what you're not testing.
 *
 *   Try to make decent comments to describe what you're testing.
 *
 */

// handles querying YQL
include_once('../YQL.php');//incude sends warning if fails, require is fatal.
include_once('../MongoToYQL_Adapter.php');

// did object instantiation work (where errors thrown)?
function dummy(){ return "<br><br>Is this thing on? Testing, testing.<br>"; }

// set up YQL connection to get current stock info
$y = new YQL;
$db = new MongoToYQL_Adapter;

echo dummy();


//*****************************************************************************












//$symbol = "LNG";
//foreach($y->populateHistoricalData($symbol) as $woof){
//    print_r($woof);
//
//}
// In MongoToYQLAdapter.php, calls YQL's populateHistoricalData()
// inserts data into histories collection
// $db->populateOneHistoryFromYQL($symbol);



