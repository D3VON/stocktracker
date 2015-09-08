<?php // YQL.php

class YQL
{
	/**************************************************************************
     	Data members -- NONE
		NOTE: I believe since this obj. will be re-used many times by the obj. 
		that has-a instance of this obj., it shouldn't have any data members to 
		store individual stock info each time, but rather simply return the
		stock info each time (as detailed JSON objects) to the calling obj.
	 **************************************************************************/

		
   /** 
    * Class constructor.
    */
	public function __construct()
	{
	}

	/**************************************************************************
     	methods
	 **************************************************************************/

	/** 
	* @desc build yql query to select a range of quotes as a JSON obj.
	*       NOTE: 1 year is maximum date range, so for more, must reset
	*             range, loop for more years, resetting range each time.
	*	
	* @param   string  	$symbol
	* @param   string  	$startDate
	* @param   string  	$endDate
	* @return  string 	$yqlQuery the query ready to submit to YQL.
	*/ 
    function buildHistoricalQuery($symbol,$startDate,$endDate)
    {
		$yqlQuery = "https://query.yahooapis.com/v1/public/yql" 
				. "?q=select%20*%20from%20yahoo.finance.historicaldata%20"
				. "where%20symbol%20%3D%20%22"
				. $symbol . "%22%20and%20startDate%20%3D%20%22"
				. $startDate . "%22%20and%20endDate%20%3D%20%22"
				. $endDate . "%22&format=json&diagnostics=true&env=store"
				. "%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
		return $yqlQuery;
    }
	
	/** 
	* @desc receive symbol, populate from YQL's history query. 
	*		NOTE 1: YQL will only let you grab 1 year's worth at a time, so have
	* 		to loop to get multiple years.
	*		NOTE 2: This function is designed to fill data for given $symbol
	*		regardless of when it's run.  It will start at the Millennium (1/1/2000), 
	*		and obtain data up to the last trading day (not including today if trading is
	*		still happening this minute).
	*	
	* @param   string	$query    the ticker symbol 
	* @return  array 	array of JSON objects, each containing one year of quotes,
	*								but last element contains this YTD
	*/ 
	function populateHistoricalData($symbol)
	{
		// $array[] = $var; // accomplishes pushing to array w/out function call overhead. 
		$endDate = date('Y-m-d'); // today's date
		
		// initialize process at beginning of millennium
		$startDate = "2000-01-01"; 
		$endDate = date('Y-m-d'); // today's date
		
		// How many years since beginning of millennium
		$date1 = new DateTime($startDate);
		$date2 = new DateTime($endDate); // today's date
		$interval = $date1->diff($date2);// years since 2000-01-01
		//echo "interval since 2000-01-01:  " . $interval->y . " years <br>"; 
		
		// this loop YQL-queries years since millennium for given symbol
		// but not current year.
		$endDate   = "2000-12-31";
		$howmanyyears = $interval->y + 1;
		$JSON_results = array(); // this needed for avoiding foreach warnings & notices
		for($i=1; $i <= $howmanyyears; $i++){
			//echo "start: $startDate     end: $endDate <br>";
			
			// 1. query YQL for this year
			// 2. push resulting JSON object into array
			
			$queryResult = $this->runQuery($this->buildHistoricalQuery($symbol,$startDate,$endDate));
			// Verify if data was returned (if not, you will be storing a NULL, 
			// and will throw Warnings and Notices accessed in a foreach loop).
			if(!is_null($queryResult->query->results)){
				//more efficient than array_push()
				$JSON_results[] = $queryResult;
			}		
				
			// advance each by 1 year
			$startDate = date('Y-m-d', strtotime("+1 year", strtotime($startDate)));
			$endDate   = date('Y-m-d', strtotime("+1 year", strtotime($endDate)));
		}
		return $JSON_results; // array of JSON objs
	}
		
	
	/** 
	* @desc build query to get 'formal' name of company. 
	*	
	* @param   string  $symbol
	* @return  string 	$yqlQuery the query ready to submit to YQL.
	*
	* example of query fully formed (runnable in browser and from this class): https://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.quote%20where%20symbol%20in%20(%22YHOO%22)&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=
	
	* Sample result: 
	{
		  "query": {
			  "count": 1,
			  "created": "2015-06-11T05:57:06Z",
			  "lang": "en-US",
			  "diagnostics": {
				  "url": 	[
							    {
							     "execution-start-time": "0",
							     "execution-stop-time": "74",
							     "execution-time": "74",
							     "content": "http://www.datatables.org/yahoo/finance/quote/yahoo.finance.quote.xml"
							    },
							    {
							     "execution-start-time": "81",
							     "execution-stop-time": "118",
							     "execution-time": "37",
							     "content": "http://download.finance.yahoo.com/d/quotes.csv?f=aa2bb2b3b4cc1c3c4c6c8dd1d2ee1e7e8e9ghjkg1g3g4g5g6ii5j1j3j4j5j6k1k2k4k5ll1l2l3mm2m3m4m5m6m7m8nn4opp1p2p5p6qrr1r2r5r6r7ss1s7t1t7t8vv1v7ww1w4xy&s=YHOO"
							    }
				   	],
				   "publiclyCallable": "true",
				   "query": {
				    "execution-start-time": "79",
				    "execution-stop-time": "119",
				    "execution-time": "40",
				    "params": "{url=[http://download.finance.yahoo.com/d/quotes.csv?f=aa2bb2b3b4cc1c3c4c6c8dd1d2ee1e7e8e9ghjkg1g3g4g5g6ii5j1j3j4j5j6k1k2k4k5ll1l2l3mm2m3m4m5m6m7m8nn4opp1p2p5p6qrr1r2r5r6r7ss1s7t1t7t8vv1v7ww1w4xy&s=YHOO]}",
				    "content": "select * from csv where url=@url and columns='Ask,AverageDailyVolume,Bid,AskRealtime,BidRealtime,BookValue,Change&PercentChange,Change,Commission,Currency,ChangeRealtime,AfterHoursChangeRealtime,DividendShare,LastTradeDate,TradeDate,EarningsShare,ErrorIndicationreturnedforsymbolchangedinvalid,EPSEstimateCurrentYear,EPSEstimateNextYear,EPSEstimateNextQuarter,DaysLow,DaysHigh,YearLow,YearHigh,HoldingsGainPercent,AnnualizedGain,HoldingsGain,HoldingsGainPercentRealtime,HoldingsGainRealtime,MoreInfo,OrderBookRealtime,MarketCapitalization,MarketCapRealtime,EBITDA,ChangeFromYearLow,PercentChangeFromYearLow,LastTradeRealtimeWithTime,ChangePercentRealtime,ChangeFromYearHigh,PercebtChangeFromYearHigh,LastTradeWithTime,LastTradePriceOnly,HighLimit,LowLimit,DaysRange,DaysRangeRealtime,FiftydayMovingAverage,TwoHundreddayMovingAverage,ChangeFromTwoHundreddayMovingAverage,PercentChangeFromTwoHundreddayMovingAverage,ChangeFromFiftydayMovingAverage,PercentChangeFromFiftydayMovingAverage,Name,Notes,Open,PreviousClose,PricePaid,ChangeinPercent,PriceSales,PriceBook,ExDividendDate,PERatio,DividendPayDate,PERatioRealtime,PEGRatio,PriceEPSEstimateCurrentYear,PriceEPSEstimateNextYear,Symbol,SharesOwned,ShortRatio,LastTradeTime,TickerTrend,OneyrTargetPrice,Volume,HoldingsValue,HoldingsValueRealtime,YearRange,DaysValueChange,DaysValueChangeRealtime,StockExchange,DividendYield'"
				   },
				   "javascript": {
				    "execution-start-time": "78",
				    "execution-stop-time": "136",
				    "execution-time": "58",
				    "instructions-used": "55574",
				    "table-name": "yahoo.finance.quote"
				   },
				   "user-time": "137",
				   "service-time": "111",
				   "build-version": "0.2.536"
				},
			 	"results": {
					"quote": {
						    "symbol": "YHOO",
						    "AverageDailyVolume": "14030800",
						    "Change": "+0.43",
						    "DaysLow": "41.69",
						    "DaysHigh": "42.31",
						    "YearLow": "32.93",
						    "YearHigh": "52.62",
						    "MarketCapitalization": "39.47B",
						    "LastTradePriceOnly": "42.06",
						    "DaysRange": "41.69 - 42.31",
						    "Name": "Yahoo! Inc.",
						    "Symbol": "YHOO",
						    "Volume": "7943818",
						    "StockExchange": "NMS"
					}
				}
		 }
	}
	*	
	*/ 
    function buildQuoteQuery($symbols)
    {	
		return "https://query.yahooapis.com/v1/public/yql"
				. "?q=select%20*%20from%20yahoo.finance.quote%20where%20symbol%20in%20(%22"
				. $symbols . "%22)&format=json&diagnostics=true&env=store"
				. "%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
    }	
	
	/** 
	* @desc receive symbol, return info from YQL's quote query. 
	*	
	* @param   string	$query    the ticker symbol 
	* 
	* @return  string 	$quoteJSONobject
	*/ 
	function getQuote($symbol)
	{
		$quoteJSONobject = $this->runQuery($this->buildQuoteQuery($symbol));
		//echo $quoteJSONobject->query->results->quote->Name;
		return $quoteJSONobject;
	}
	
	/** 
	* @desc Use curl to run queries in Yahoo's YQL API. 
	*	
	* @param   string  $yqlQuery    The fully formed YQL string being decoded 
	* 
	* @return  string 	Returns the value encoded in json in appropriate PHP type.
	*/ 
	function runQuery($yqlQuery)
	{
		//echo "<br>$yqlQuery<br>";
		// run query via curl
		$ch = curl_init($yqlQuery);
		if($ch) 
		{   // various errors could happen. check those as much as possible
			if( !curl_setopt($ch, CURLOPT_URL, $yqlQuery) ) 
			{ 		
				curl_close($ch); // to match curl_init() 
				echo "FAIL: curl_setopt(CURLOPT_URL)"; 
			} 
			if( !curl_setopt($ch, CURLOPT_HEADER, 0) ) 
			{
				echo "FAIL: curl_setopt(CURLOPT_HEADER)"; 
			} 
			if( !curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0) ) 
			{
				echo "FAIL: curl_setopt(CURLOPT_SSL_VERIFYHOST)"; 
			} 
			if( !curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0) ) 
			{
				echo "FAIL: curl_setopt(CURLOPT_SSL_VERIFYPEER)"; 
			} 
			if( !curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1) ) 
			{
				echo "FAIL: curl_setopt(CURLOPT_FOLLOWLOCATION)"; 
			} 
			// flag to pass result to variable instead of to a file.
			if( !curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) ) 
			{
				echo "FAIL: curl_setopt(CURLOPT_RETURNTRANSFER)"; 
			}
			if( !($response = curl_exec($ch)) ) 
			{
				echo "FAIL: curl_exec()"; 
				echo '<br>Curl error: ' . curl_error($ch);
			} 			
			curl_close($ch); 
		} 
		else
		{
			echo "FAIL: curl_init()"; 
		}	
			
		
		/* The PHP MongDB Driver accepts only PHP arrays for inserts and queries
		 * (see here: http://www.php.net/manual/en/mongo.queries.php)
		* So you need to convert your JSON to an array.
		*/
		// json_decode() converts a JSON encoded string into a PHP variable (a multi-dimensional array).
		return json_decode($response);//, TRUE);
		//return $response;
	}
	
}	

/*
	// test code here, making it a driver to test this class
	echo "I'm a driver now!";
	$y = new YQL;
	$JSON = $y->populateHistoricalData("TWTR");
					echo "<pre>";
					print_r($JSON);
					echo "</pre>";
*/	

?>
