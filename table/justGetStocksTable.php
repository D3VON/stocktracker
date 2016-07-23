<?php // justGetStocksTable.php
	//echo "<br>here is var_dump of POST:<br>";
	//var_dump($_POST);

	require_once('StocksTable.php');
	require_once('../MongoToYQL_Adapter.php');

	if (ctype_alnum($_POST['owner'])) {//Returns TRUE if every character in text is either a letter or a digit, FALSE otherwise.
		$owner = $_POST['owner'];
	} else {
		echo "Erroneously formed owner name.  Please try again.";
		exit;
	}

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
	
	$stocks = $mongo->getAllStocksByOwner($owner);
	echo $doTable->makeStocksTable($stocks);
?>