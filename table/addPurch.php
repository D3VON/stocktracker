<?php
	
// 	echo "<br>here is var_dump of POST:<br>";
// 	var_dump($_POST);
{
	//echo "requiring<br>";
	require_once('StocksTable.php');
	require_once('../MongoToYQL_Adapter.php');

	// check all user input!!!
	//$moneypattern =  '/^\d+(?:\.\d{0-3})?$/';
	$moneypattern = '#[0-9]+(\.[0-9]{0-3})?#';
	$datepattern = '/(0[1-9]|1[012])[- \/.](0[1-9]|[12][0-9]|3[01])[- \/.](19|20)\d\d/';
//wrong: $datepattern = "#^(0[1-9]|1[0-2])/(0[1-9]|[1-2][0-9]|3[0-1]/[0-9]{4})$#";
	/*
	 * Explanation:
	 * 			+		# one or more of the previous thing
				\b      # word boundary assertion
				\d{1,3} # 1-3 digits
				(?:     # followed by this group...
				 ,?     # an optional comma
				 \d{3}  # exactly three digits
				)*      # ...any number of times
				(?:     # followed by this group...
				 \.     # a literal dot
				 \d{2}  # exactly two digits
				)?      # ...zero or one times
				\b      # word boundary assertion
				$ 		# end of string <-----------won't work for me...maybe variable is coming in padded with spaces

	another way, similar:

	^[0-9]+(\.[0-9]{0-3})?$
			And since regular expressions are horrible to read, much less understand,
			here is the verbose equivalent:

			^                   # Start of string. <-----------won't work for me...maybe variable is coming in padded with spaces
			[0-9]+              # Must have one or more numbers.
			(                   # Begin optional group.
				\.              # The decimal point, . must be escaped,
								# or it is treated as "any character".
				[0-9]{1,2}      # One or two numbers.
			)?                  # End group, signify it's optional with ?
			$                   # End of string. <-----------won't work for me...maybe variable is coming in padded with spaces

	 */
	if (ctype_alpha($_POST['symbol'])) {//Returns TRUE if every character in text is a letter from the current locale, FALSE otherwise.
		$symbol = strtoupper($_POST['symbol']);
	} else {
		echo "Erroneously formed symbol.  Please try again.";
		exit;
	}
	if (ctype_digit($_POST['quant'])) {//Returns TRUE if every character in the string text is a decimal digit, FALSE otherwise.
		$quant = $_POST['quant'];
	} else {
		echo "Erroneously formed quantity.  Please try again.";
		exit;
	}
	if ( preg_match($moneypattern, $_POST['price']) == '1') { //preg_match() returns 1 if the pattern matches given subject, 0 if it does not, or FALSE if an error
		$price = $_POST['price'];
	} else {
		echo "Erroneously formed price.  Please try again.";
		exit;
	}
	if ( preg_match($datepattern, $_POST['datepicker']) == '1') { //preg_match() returns 1 if the pattern matches given subject, 0 if it does not, or FALSE if an error
		$date = $_POST['datepicker'];
	} else {
		echo "Erroneously formed date.  Please try again.";
		exit;
	}
	if (preg_match($moneypattern, $_POST['fee']) == '1') { //preg_match() returns 1 if the pattern matches given subject, 0 if it does not, or FALSE if an error
		$fee = $_POST['fee'];
	} else {
		echo "Erroneously formed fee.  Please try again.";
		exit;
	}
	if (ctype_alnum($_POST['account'])) {//Returns TRUE if every character in text is either a letter or a digit, FALSE otherwise.
		$account = $_POST['account'];
	} else {
		echo "Erroneously formed account.  Please try again.";
		exit;
	}
	if (ctype_alnum($_POST['owner'])) {//Returns TRUE if every character in text is either a letter or a digit, FALSE otherwise.
		$owner = $_POST['owner'];
	} else {
		echo "Erroneously formed owner name.  Please try again.";
		exit;
	}

	try {
		$StocksTableObj = new StocksTable;
	} catch (Exception $e) {
		echo 'Caught exception: ', $e->getMessage(), "<br>";
		exit;
	}

	try {
		$mongo = new MongoToYQL_Adapter;
	} catch (Exception $e) {
		echo 'Caught exception: ', $e->getMessage(), "<br>";
		exit;
	}

	$stocks = $mongo->addPurchase($symbol, $quant, $price, $date, $fee, $account, $owner);
	echo $StocksTableObj->makeStocksTable($owner,$stocks);
	//echo $StocksTableObj->makeStocksTBODY($stocks);
}
?>