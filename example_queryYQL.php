<?php // example_queryYQL.php

include_once('YQL.php');


/*************************************************************/
// Query YQL for quote
/*************************************************************/
$y = new YQL;
$symbol = "YHOO";



/* NOTE: // YQL class receives JSON from YQL query, but converts the JSON 
 * into a PHP variable (a multi-dimensional array), so it's easy to weedle out 
 * pieces you need with nice PHP operators. 
 */
$resultArray = $y->getQuote($symbol);

echo "this should be the name of the stock:<br>";
$name = $resultArray->query->results->quote->Name;
echo "<br>";
echo $name;
echo "<br>";
// this works "fast" since it's just a little data for just one stock


/*************************************************************/
// dump out the JSON that was returned by the YQL query
/*************************************************************/

echo "<pre>";
print_r($resultArray); 
echo "</pre>";

// next step: make an example to save quote to database

?>