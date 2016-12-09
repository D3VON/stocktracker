<?php

// 	echo "<br>here is var_dump of POST:<br>";
// 	var_dump($_POST);
{
    require_once('../MongoToYQL_Adapter.php');

    if (ctype_alpha($_POST['symbol'])) {//Returns TRUE if every character in text is a letter from the current locale, FALSE otherwise.
        $symbol = strtoupper($_POST['symbol']);
    } else {
        echo "Erroneously formed symbol.  Please try again.";
        exit;
    }

    try {
        $mongo = new MongoToYQL_Adapter;
        $mongo->addNewHistoryToMongo($symbol);
        echo "Stock was added to history.";
    } catch (Exception $e) {
        echo 'Caught exception: ', $e->getMessage(), "<br>";
        exit;
    }

}
?>