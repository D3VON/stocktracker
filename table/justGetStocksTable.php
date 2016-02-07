<?php // justGetStocksTable.php
	//echo "woof<br>";
	
	//echo "<br>here is var_dump of POST:<br>";
	//var_dump($_POST);
	
	//echo "requiring<br>";
	require_once('StocksTable.php');
	require_once('../MongoToYQL_Adapter.php');
	
	try{
		$doTable = new StocksTable;
	} catch (Exception $e) {
		echo 'Caught exception: ',  $e->getMessage(), "<br>";
	}
	
	try{ 
		$mongo = new MongoToYQL_Adapter;
	} catch (Exception $e) {
    	echo 'Caught exception: ',  $e->getMessage(), "<br>";
	}	
	
	$stocks = $mongo->getAllStocksByOwner($_POST['owner']);
	echo $doTable->makeStocksTable($stocks);
?>