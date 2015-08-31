<?php
class Stock
{
	/**************************************************************************
     	Data members 
	 **************************************************************************/
	private $symbol = "";
	private $min = 0; // for D3 graph use, if any
	private $max = 0; // for D3 graph use, if any
	private $name = "";
	private $quantity = 0;
	private $purchaseDate = "";
	private $purchasePrice = "";
	private $startDate = "";
	private $priceData = ""; // string for Javascript to use
	private $account = ""; // account where this stock is held (e.g. SEP, IRA, etc)
		
   /** 
    * Class constructor.
    */
	public function __construct()
	{
		$date = new DateTime(); // get today's date
		$this->endDate = $date->format('Y-m-d'); // today's date
	}

	/**************************************************************************
     	methods, getters, setters 
	 **************************************************************************/
    function setSymbol($s)	{ $this->symbol = $s; }
	function getSymbol()	{ return $this->symbol; }
	
    function setMax($m)		{ $this->max = $m; }
	function getMax()		{ return $this->max; }
	
    function setMin($m)		{ $this->min = $m; }
	function getMin()		{ return $this->min; }
	
    function setAccount($a)	{ $this->account = $a; }
    function getAccount()	{ return $this->account; }
	
    function setQuantity($q){ $this->quantity = $q; }
    function getQuantity()	{ return $this->quantity; }
	
    function setStartDate($sd){ $this->startDate = $sd; }
    function getStartDate()	{ return $this->startDate; }
	
    function setEndDate($ed){ $this->endDate = $ed; }
    function getEndDate()	{ return $this->endDate; }
		
	
    function getQuery()
    {
		$yqlQuery = "https://query.yahooapis.com/v1/public/yql" 
				. "?q=select%20*%20from%20yahoo.finance.historicaldata%20"
				. "where%20symbol%20%3D%20%22"
				. $this->getSymbol() . "%22%20and%20startDate%20%3D%20%22"
				. $this->getStartDate() . "%22%20and%20endDate%20%3D%20%22"
				. $this->getEndDate() . "%22&format=json&diagnostics=true&env=store"
				. "%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
		return $yqlQuery;
    }
	

	/** 
	* @desc Use curl to run queries in Yahoo's YQL API. 
	*	
	* @param   string  $yqlQuery    The fully formed YQL string being decoded 
	* @return  string 	Returns the value encoded in json in appropriate PHP type.
	*/ 
	function runQuery($yqlQuery)
	{
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
			
		return json_decode($response, true); // true returns array not object
	}
	

    function queryName()
    {
		$query = "https://query.yahooapis.com/v1/public/yql"
				. "?q=select%20*%20from%20yahoo.finance.quote%20where%20symbol%20in%20(%22"
				. $this->getSymbol() . "%22)&format=json&diagnostics=true&env=store"
				. "%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";

		$recordArr = $this->runQuery($query);	
		
		$this->name = $recordArr->query->results->quote->Name;		
    }	
	
	
	// add sysmbol to our database
    function addSymbol()
    {	
		// perhaps run YQL in body here to get data, then add data to tables
		// perhaps call various helper functions
    }	
	
	// remove sysmbol to our database
    function removeSymbol()
    {	
    }
	
	// does sysmbol exist already in our database
    function existSymbol()
    {	
    }	
	
	// does sysmbol exist already in our database
    function populateHistory()
    {	
		//go as far back as you can with YQL
		// figure out how far back you can really go in YQL (stackoverflow)
		// maybe find another API for historical data.  Maybe weedle out of google somehow???
    }
	
	
    function setName($n)
    {
		$this->name = $n;
    }
	
    function getName()
    {
		if(!isset($this->name)) $this->queryName();
		
		// what to do if null?  maybe view can handle that... or controller
		
		return $this->name;
    }
	
	
	function getData()
	{
		$recordArr = $this->runQuery($this->getQuery());
		
		// set up min/max for Y axis bounds
		// also store (x,y) coordinates to array
		$min = 99999;
		$max = 0;
		foreach($recordArr->query->results->quote as $Q){
			if ($min > $this->getQuantity()*$Q->Close)
			{
				$min = $this->getQuantity()*$Q->Close;
			}
			if ($max < $this->getQuantity()*$Q->Close)
			{
				$max = $this->getQuantity()*$Q->Close;
			}
			$dt = strtotime($Q->Date);//nice! rickshaw uses seconds, not milliseconds!	
			$arr1[] = "{ x: $dt, y: ". $this->getQuantity()*$Q->Close ." },";	
			// for showing date in human readable format
			//$arr1[] = "{ x: $Q->Date, y: ". $this->getQuantity()*$Q->Close ." },";
		} 
		$this->setMin($min-($min/20));
		$this->setMax($max+($max/20));
		// Note: YQL results are in reverse-chronological order.
		// Loop to make the order chronological,
		// to make them usable in a graph. 		
		$priceData = "";
		while($arr1 != NULL){
			$priceData .= array_pop($arr1);	
		}		
		/* Note: should be eliminated and array_pop() should be used instead. 
		   array_pop() pops and returns the last value of the array, shortening 
		   the array by one element. If array is empty (or is not an array), 
		   NULL will be returned. Will additionally produce a Warning when 
		   called on a non-array.
		   
		   Note: the chronological problem is solved using psql's  ORDER BY thedate ASC
		 */  
		
		return $priceData;				
    }
	
	/** 
	* @desc check if symbol already in user's list. 
	*	
	* @param   string	$symbol    the ticker symbol 
	* @return  bool 	
	*/ 
	function symbolRedundant($symbol)
	{
		//////////////////////////////////////////////finish it
	}	
		
}


	// test code here, making it a driver to test this class
	//echo "I'm a driver now!";
	
	
	
	
	

?>