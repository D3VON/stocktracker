<?php // InvestDB.php

/* 	*************************************************************
	Connection and methods to interact with investments database.
	Mainly, store YQL query results (historical data for stocks).
	Also, retrieve those stored data for presentation to user.
	*************************************************************

		select * from purchases where symbol = 'TRIP';

		 symbol |       name        | quantity | purchdate  | pricepershare 
		--------+-------------------+----------+------------+---------------
		 TRIP   | TripAdvisor, Inc. |       55 | 2014-05-22 |         87.64
		 

		select * from historicquotes;

		 symbol |  thedate   | closevalue 
		--------+------------+------------
		 WOOF   | 2014-05-22 |         10
  
*/
// figure out how to make globals.php more secure. 
// --depth in directory structure??
// --google path include_once php
include_once("global.php");
//include_once('../global.php'); 
include_once('YQL.php');
// include() function generates a warning; script will continue execution. 
// require() generates a fatal error

class InvestDB
{
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
        //$this->dbconn = new MongoClient("mongodb://${username}:${password}@localhost/myDatabase");
		$this->dbconn = pg_connect("host="	 .DB_HOST
								." dbname="	 .DATABASE
								." user="	 .DB_USERNAME
								." password=".DB_USERPASS
						) 
						or die('connection failed');
							
		// TODO: object pool (for practice!!)	
	}

   /** 
    * Class destructor.
    */
   function __destruct() {
		//print "Destroying connection to \$dbconn, \$result, etc...<br>";
	   	   // THIS   IS   MORE   TROUBLE   THAN   IT'S   WORTH
		// Free resultset
		//if(isset($this->result)) pg_free_result($this->result);
		// Closing connection
		//if(isset($this->dbconn)) pg_close($this->dbconn);	   
   }

	/** add sysmbols to our database
	 * @param $symbols array of stock ticker symbols
	 */	
    function addManySymbols($symbols){
		foreach($symbols as $s){
			//TODO -- just loop through and call addOneSymbol
		}
	}

	/** add a sysmbol to our database
	 *
	 * That means running a YQL query repetitively, storing the returned JSON 
	 * object, then looping over that result to put it all into the local database.
	 *
	 * @param $symbol the stock ticker symbol
	 * @param $date the date it was purchased
	 * @param $quantity the quantity purchased of the symbol
	 * @param $price the price it was purchased
	 * @param $fee the fee that was paid to purchase the thing
	 */	
    function addOneSymbol($symbol, $date, $quantity, $price, $fee){	
		
		switch ($this->alreadyHaveSymbol($symbol)) {
			case "0": // error with query; inconclusive.
				echo "query did not execute, here's the error:". pg_last_error()."<br>";
				break;
			case "1": // symbol doesn't exist in our records, safe to add
				//echo "=========== 0 records, so, safe to add to the db ============<br>";
				/*************************************************************/
				// 1. Grab quote from YQL
				/*************************************************************/
				$y = new YQL;
				$JSON = $y->getQuote($symbol);
				
				/*************************************************************/
				// 2. Populate purchases table
				/*************************************************************/
				
				// query won't take "$JSON->query->results->quote->Name" directly
				// because it will try to pass just "$JSON", together with the 
				// literal string "->query->results->quote->Name".  So, have to 
				// do preliminary step of saving whole resolution into a string variable. 
				echo "<br>";
				$name = $JSON->query->results->quote->Name;
				echo $name;
				echo "<br>";
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
				// Insert must be unique each time (for Symbol)
				$query = "INSERT INTO purchases VALUES"
				."		('$symbol', '$name', $quantity, '$date', $price, $fee)";	
				$this->result = pg_query($query) or die('Query failed: ' . pg_last_error());
				if  (!$this->result) {
					echo "<br>BADNESS HAPPENED<br>";
				}else{
					//echo "<br>successfully added a record to purchases table<br>";
				}

				/*************************************************************/
				// 3. Grab history from YQL
				/*************************************************************/
				// it's an array of JSON objects!
				$JSON = $y->populateHistoricalData($symbol);

				/*************************************************************/
				// 4. Populate historical table
				/*************************************************************/
				
				foreach($JSON as $json){ // about 15 JSON elements in that array
										
					//if (!is_array($json->query->results->quote)) die("<br>That $json isn't an array<br>");
					
					foreach ($json->query->results->quote as $Q){
										
						// So there's no unpleasantness with object->pointing->to->string,
						// make a nice clean string to give to the query. 
						$close = $Q->Close;
						$date  = $Q->Date;
						
						$query = "INSERT INTO historicquotes VALUES"
						."		('$symbol', '$date', $close)";
						$this->result = pg_query($query);
						if(!$this->result){
							 echo 'Historicalquotes query failed: ' . pg_last_error();
						}
						//if(isset($json->query->results->quote))
					}
				}	
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
	
				break;
			case "2": // already exists
				echo "don't add.  It already exists.<br>";
				break;
			default:
				echo "This is the default. I have no idea what happened for it go get this far.";
		}
				
    }	
	

	
	
	/** Build a string of coordinate points for a D3 graph. 
	 * 
	 * Note: Arguments conform to PHP's strtotime()-acceptable arguments
	 *
	 * @param $symbol 		string stock symbol
	 * @param $numPeriods  	int number of [days | months | years] of period 
	 * @param $typePeriods 	string type of period [days | months | years]
	 * @param $endDate 		string end date of the graph
	 *
	 * @return associative array containing D3 graph information as follows:
	 *					1. string of the symbol
						2. string of coordinates
	 *					3. int of min bound of graph
	 *					4. int of max bound of graph
	 */	
    function getD3Coordinates($symbol, $numPeriods, $typePeriods, $endDate, $quant=1) 
    {	
		// build the start date from arguments given (calculated back from $endDate)
		$startDate = date('Y-m-d', strtotime("-$numPeriods $typePeriods", strtotime($endDate)));
				
		$q = "select thedate, closevalue from historicquotes where symbol = '$symbol'
and thedate between '$startDate' and '$endDate' ORDER BY thedate ASC";
		
		$result = pg_query($q);
		if (!$result) {
			echo "An error occurred in getD3Coordinates.  Tell that to the developer.<br>";
		}
		
		// set up min/max for Y axis bounds
		// also store (x,y) coordinates to $priceData string
		$min = 99999;
		$max = 0;	
		$priceData = "";
		while ($row = pg_fetch_row($result)) {
			if ($min > $quant*$row[1])
			{
				$min = $quant*$row[1];
			}
			if ($max < $quant*$row[1])
			{
				$max = $quant*$row[1];
			}
			$dt = strtotime($row[0]);//nice! rickshaw uses seconds, not milliseconds!	
			$priceData .= "{ x: $dt, y: ". $row[1] ." },";	
		}	
		
		$coordInfo = array();
		$coordInfo['symbol'] = $symbol;	
		$coordInfo['coords'] = $priceData;	
		$coordInfo['min'] = number_format(($min-($min/20)),2);	
		$coordInfo['max'] = number_format(($max+($max/20)),2);	
		return $coordInfo;
	}
	
	
	
	
	// remove sysmbol to our database
    function removeSymbol()
    {	
    }
	
	/** Just get a list of stock symbols in the db. 
	 *
	 * @return  an array of strings, each element is a symbol
	 */	
    function getSymbols()
    {	}

	/** Query db for data needed to populate table (currently in stocks.php). 
	 *
	 * I regret putting such a specific query here, as it violates the 
	 * design patterns principal of "closed for modification" (since I expect
	 * the table will change and the database will also change).
	 *
	 * @param $symbol the stock ticker symbol
	 * @param $date the date it was purchased
	 * @param $quantity the quantity purchased of the symbol
	 * @param $price the price it was purchased
	 * @param $fee the fee that was paid to purchase the thing
	 *
	 * @return  $JSONarray an array of JSON objects, each having a row's data
	 */	
    function populateTable()
    {	
		/*
			 symbol |  thedate   | closevalue | symbol |       name        | quantity | purchdate  | pricepershare | fee  
			--------+------------+------------+--------+-------------------+----------+------------+---------------+------
			 mu     | 2014-07-11 |      32.80 | mu     | Micron Technology |       55 | 2014-04-22 |         55.25 | 7.95
			 
			Grab newest quotes from historicquotes table, 
			then join them with info from purchases table
			 
			Flaw: if there's only one stock updated to (say) today, 
			but all the other stocks most recent update was yesterday, 
			we will only get one stock in result. 
			  
			Likewise, if one stock is not updated for today (and all the rest are)
			then we will miss that one not-updated stock
			)
			 
			select * from 
				(select * from historicquotes 
					where thedate = 
						(select max (thedate) from historicquotes)
				) as h 
			join 
				(select * from purchases) as p 
			on 
				h.symbol = p.symbol;


		 */
		$q = 	"select * from (select * from historicquotes where thedate = (select max (thedate) from historicquotes)) as h join (select * from purchases) as p on h.symbol = p.symbol";
			
		$this->result = pg_query($q);
		return $this->result;
		/* obsolete
		$rows[] = array();
		while($row=pg_fetch_assoc($this->result)){
			
			echo $row['symbol'] . ", " . $row['thedate'] . ", " . $row['closevalue'] . ", " . $row['name'] . ", " . $row['quantity'] . ", " . $row['purchdate'] . ", " . $row['pricepershare'] . ", " . $row['fee'] . "<br>";
		}
		*/
    }
	
	
	// does sysmbol exist already in our database
    function alreadyHaveSymbol($symbol)
    {	
		$query = "select 'symbol' from purchases where symbol='$symbol'";
		$this->result = pg_query($this->dbconn,$query);
		if  (!$this->result) {
			return 0; // error with query; inconclusive.
		}		// pg_num_rows() leaves result-set cursor pointed to first row so you can use it in a loop.
		elseif (pg_num_rows($this->result) == 0) {
			return 1; // doesn't exist, safe to add
		}
		return 2; // already exists
    }	
	
	
	
	function printTable($resource){
		// $mycell=$results[$rownumber][$columname];
	
		// Print results in HTML table (very raw, b.c. w/out other html tags)
		echo "<table>\n";
		while ($line = pg_fetch_array($resource, null, PGSQL_ASSOC)) {
			echo "\t<tr>\n";
			foreach ($line as $col_value) {
				echo "\t\t<td>$col_value</td>\n";
			}
			echo "\t</tr>\n";
		}
		echo "</table>\n";
	}
	
}

 //FOR TESTING THIS OBJECT BY ITSELF:
	// Test this object independent of any other 
	//echo "I'm a driver now<br>";
/*	
	$investdbObject = new InvestDB;
	$message = $investdbObject->alreadyHaveSymbol("TRIP");
	echo "testing for TRIP: ". $message;
	echo "<br>";
	$message = $investdbObject->alreadyHaveSymbol("YHOO");
	echo "testing for YHOO: ". $message;
	echo "<br>";
	$investdbObject->addOneSymbol("HD", "2013-09-19", 18, 78.30, 7.95);
	$investdbObject->addOneSymbol("AAPL", "2014-06-02", 35, 90.51, 7.95);
	$investdbObject->addOneSymbol("DIS", "2014-05-28", 25, 83.57, 7.95);
	echo "<br>";
		
	//echo "<br>";
	//$investdbObject->populateTable();
	//echo "<br>";
	$investdbObject = new InvestDB;
	$result = $investdbObject->getD3Coordinates("HD", 3, "months", date('Y-m-d') );
					echo "<pre>";
					print_r($result);
					echo "</pre>";
		*/

	
?>

