<?php
	
	// checked rows are coming over (via POST) as just a comma separated list that i have to 
	// explode into an array for use in the removePurchase() function 
	$remove = explode(',', $_POST['remove']);
	//var_dump($remove);

	// check all user input!!!
	foreach($remove as $id){
		if (!ctype_alnum($id)) { // should be just mongo ids, so only alpha/text
			echo "Erroneously formed identifying ids.  Please try again.";
			exit;
		}
	}
	if (ctype_alnum($_POST['owner'])) {
		$owner = $_POST['owner'];
	} else {
		echo "Erroneously formed owner name.  Please try again.";
		exit;
	}

	require_once('StocksTable.php');
	require_once('../MongoToYQL_Adapter.php');
	
	try{
		$doTable = new StocksTable;
	} catch (Exception $e) {
		echo 'Caught exception: ',  $e->getMessage(), "<br>";
		exit;
	}
	
	try{ 
		$mongo = new MongoToYQL_Adapter;
	} catch (Exception $e) {
    	echo 'Caught exception: ',  $e->getMessage(), "<br>";
		exit;
	}

	$stocks = $mongo->removePurchase($remove, $owner);
	//echo "<br>back in removePurch.php; now running makeStocksTable()<br>";
	echo $doTable->makeStocksTBODY($stocks);
?>