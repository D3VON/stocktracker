<?php

	require_once('../MongoToYQL_Adapter.php');
	require_once('StocksTable.php');


//echo "REQUEST_METHOD:" . $_SERVER['REQUEST_METHOD'] . "<br>";
//echo "SERVER_ADDR:" . $_SERVER['SERVER_ADDR'] . "<br>";
//echo "HTTP_ACCEPT:" . $_SERVER['HTTP_ACCEPT'] . "<br>";
//echo "HTTP_REFERER:" . $_SERVER['HTTP_REFERER'] . "<br>";
//echo "HTTP_USER_AGENT:" . $_SERVER['HTTP_USER_AGENT'] . "<br>";
//echo "REMOTE_ADDR:" . $_SERVER['REMOTE_ADDR'] . "<br>";
//echo "REMOTE_PORT:" . $_SERVER['REMOTE_PORT'] . "<br>";


	$owner = $_POST['owner'];
	if($owner == ""){
		echo "<br>No owner specified.  Please input an owner.<br>";
		exit;
	}elseif(!ctype_alnum($owner)){
		echo "<br>Not an owner-name.  Please input an owner.<br>";
		exit;
	}else{
		try{
			$mongo = new MongoToYQL_Adapter;
		} catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "<br>";
			exit;
		}
			
		try{
			$StocksTableObj = new StocksTable;
		} catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "<br>";
			exit;
		}
			
		$stocks = $mongo->getAllStocksByOwner($owner);
		echo $StocksTableObj->makeStocksTable($owner,$stocks);
			
	}
