<?php

	require_once('../MongoToYQL_Adapter.php');
	require_once('StocksTable.php');
	
	$owner = $_POST['owner'];
	
	if($owner == ""){
		echo "<br>No owner specified.  Please input an owner.<br>";
		exit;
	}elseif(!ctype_alnum($owner)){
		echo "$owner is owner name. <br>";
		echo "<br>Not an owner-name.  Please input an owner.<br>";
		exit;
	}else{
		try{
			$mongo = new MongoToYQL_Adapter;
		} catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "<br>";
		}
			
		try{
			$tableMaker = new StocksTable;
		} catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "<br>";
			exit;
		}
			
		$stocks = $mongo->getAllStocksByOwner($owner);
		//here is where I would update the stocks values
		echo $tableMaker->makeStocksTable($owner,$stocks);
			
	}
