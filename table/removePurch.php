<?php
	//echo "woof<br>";
	
	//echo "<br>here is var_dump of POST:<br>";
	//var_dump($_POST);
	//echo "<br>";
	
	// checked rows are coming over (via POST) as just a comma separated list that i have to 
	// explode into an array for use in the removePurchase() function 
	$remove = explode(',', $_POST['remove']);
	//var_dump($remove);

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
	
	$stocks = $mongo->removePurchase($remove, $_POST['owner']);
	//echo "<br>back in removePurch.php; now running makeStocksTable()<br>";
	echo $doTable->makeStocksTBODY($stocks);
?>