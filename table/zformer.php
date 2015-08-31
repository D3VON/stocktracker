<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html>
<head>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>

<link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css">

		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<!-- script src="js/bootstrap.min.js"></script -->	
		<link type="text/css" rel="stylesheet" href="stocks.css">
<style type="text/css">
#box_form > *{
    font-size:12px;
    margin-bottom:15px;
}
#box_form > p > label{
    display:block;
    font-size:18px;
}
#box_form > p > input, #box_form > p > textarea{
        width:300px;
}
</style>
<script type='text/javascript'>
$(document).ready(function() {
    $('#box_form').dialog({
        autoOpen: false,
        height: 375,
        width: 350,
        modal: true,
        buttons: [
            {
            text: "Cancel",
            click: function() {
                $(this).dialog("close");
            }},
        {
            text: "Submit",
            click: function() {
                $('#zFormer').submit();
            }}
        ]
    });
    $('#clicky').button().click(function(e){
        $('#box_form').dialog('open');
    });
});
</script>
</head>
<body>

		<h1>Stock Tracker -- Pseudo Account</h1>
		<p>
		  A 'view' of a pseudo-portfolio of stocks.
		</p>

		<!-- h2>June 22, 2014</h2 -->
		<p><img src="images/bullbear.gif" alt="bull and bear"></p>
		<p>
		  Here's a table presenting some stocks: 
		</p>

		<table>
		  <caption>
			Stocks being monitored
		  </caption>
		  <tr>
			<th colspan="2">Individual (Fidelity)</th>
			<th colspan="4">Most Recent</th>
			<th colspan="2">Change Since Close</th>
			<th colspan="3">Change Since Purchase</th>
			<th colspan="3">Cost Basis</th>
		  </tr>
		  <tr>
			<th>Symbol</th>
			<th>Name</th>
			<th>Quantity</th>
			<th>Price</th>
			<th>Change</th>
			<th>Value</th>
			<th>$</th>
			<th>%</th>
			<th>$</th>
			<th>%</th>
			<th>%peryear</th>
			<th>Per Share</th>
			<th>Total</th>
			<th>Date Acquired</th>
		  </tr>
		  

<?php		

	require_once('Stock.php');
	//$colors = array("#0000FF", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33");
	$date = new DateTime(); // get today's date		
	$startDate = "2014-06-01";
	$endDate = $date->format('Y-m-d'); // today's date
	$stockNames = array("TRIP","GOOGL","GOOG","TGT","AAPL","LNKD");
	$amt = 1;
	$min = 9999;
	$stockObjects = array();
	foreach($stockNames as $symb){
	
		$theStockObj = new Stock();
		$theStockObj->setSymbol($symb);
		$theStockObj->setQuantity($amt);
		$theStockObj->setStartDate($startDate);
		$theStockObj->setEndDate($endDate);
		$theStockObj->setAccount("ScotTrade IRA");		
		$stockObjects[] = $theStockObj;
		$name = $theStockObj->getName();

		// set up div to hold each graph
		$stockDiv = ""
	
		// "stockdiv" misnamed as I develop this table (replaced the chart div as I cobble this together)
		."<tr>"
		."	<td>".$symb."</td>"
		."	<td class=\"center\">".$name."</td>"
		."	<td class=\"center\">".$amt."</td>"             
		."	<td class=\"right\">current$</td>"
		."	<td class=\"right\">calcChng</td>"
		."	<td class=\"center\">calc$amt</td>"
		."	<td class=\"center\">dontneed</td>"
		."	<td class=\"center\">dontneed</td>" 
		."	<td class=\"center\">totChng$</td>"
		."	<td class=\"center\">totChng%</td>" 
		."	<td class=\"center\">YrlyChg%</td>" 
		."	<td class=\"center\">price$</td>"
		."	<td class=\"center\">calcTot$</td>"
		."	<td class=\"center\">dateAcq</td>"
		."</tr>";
		
		// add amount to database / need form to do so...
		//OY! need individual purchase amts!  e.g., goog: 1 on 3/2/14, 5 on 6/19/14
		// need to grab "current price" from db 
		// need to grab "yesterday's price" from db 
		// need to grab "price bought" from db 
		// need to grab "date acquired" from db 
		
		// this is where the foregoing block is 'echoed' to the html page
		echo $stockDiv;
		//echo "<br>".$stockDiv."<br><hr>";
		
	}	
	

    echo "</table>";
	
?>	  
		  

		<form id="addsymbol" method="POST" action="addstock.php" name="addstock">	
		<div id="box_form">
			<p>
				<label for="symbol">Symbol:</label>
				<input type="text" value="XYZ" name="symbol_name">
			</p>
			<p>
				<label for="price">Price for each: </label>
				<input type="text" value="1.00" name="price_each">
			</p>
			<p>
				<label for="date_purch">Date Purchased: </label>
				<input type="text" value="12/31/1999" name="date_purch">
			</p>
			<p>
				<label for="description">Which account: </label>
				<select>
				  <option value="Fidelity_Individual">Fidelity Individual</option>
				  <option value="Fidelity_SEP">Fidelity SEP</option>
				  <option value="Fidelity_Roth">Fidelity Roth</option>
				  <option value="Scottrade_IRA">Scottrade IRA</option>
				</select>
			</p>
		</div>
		</form>
	<input type="button" id="clicky" value="Add a symbol">
	
<?php

	$dbconn = pg_connect("host=localhost dbname=investments user=php password=Susquehanna") or die('connection failed');
		
	// Performing SQL query
	//$query = "select * from purchases where symbol = 'TRIP'";
	$query = "select * from purchases";
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	
	// can't just do the following b.c. it's an object thingy
	//echo $result;
		
	// Printing results in HTML
	echo "<table>\n";
	while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
		echo "\t<tr>\n";
		foreach ($line as $col_value) {
			echo "\t\t<td>$col_value</td>\n";
		}
		echo "\t</tr>\n";
	}
	echo "</table>\n";

	// Free resultset
	pg_free_result($result);

	// Closing connection
	pg_close($dbconn);


?>
		  
		  
		  
		  
		  
		  
		  

		<!-- form id="addsymbol" method="POST" action="addstock.php" name="addstock">	
		<div id="box_form">
			<p>
				<label for="symbol">Symbol:</label>
				<input type="text" value="XYZ" name="symbol_name">
			</p>
			<p>
				<label for="price">Price for each: </label>
				<input type="text" value="1.00" name="price_each">
			</p>
			<p>
				<label for="date_purch">Date Purchased: </label>
				<input type="text" value="12/31/1999" name="date_purch">
			</p>
			<p>
				<label for="description">Which account: </label>
				<select>
				  <option value="Fidelity_Individual">Fidelity Individual</option>
				  <option value="Fidelity_SEP">Fidelity SEP</option>
				  <option value="Fidelity_Roth">Fidelity Roth</option>
				  <option value="Scottrade_IRA">Scottrade IRA</option>
				</select>
			</p>
		</div>
		</form>
	<input type="button" id="clicky" value="Add a symbol" -->

						<!--form id="zFormer" method="POST" action="former.php" name="former">
						<div id="box_form">
							<p>
								<label for="z_name">Your Name:</label>
								<input type="text" value="Adam Panzer" name="z_name">
							</p>
							<p>
								<label for="z_requestor">Your Email Address: </label>
								<input type="text" value="apanzer@zendesk.com" name="z_requester">
							</p>
							<p>
								<label for="z_subject">Subject: </label>
								<input type="text" value="Who needs a subject?" name="z_subject">
							</p>
							<p>
								<label for="z_description">Description: </label>
								<textarea name="z_description">I have this pain in my knee.</textarea>
							</p>
						</div>
						</form>
						<input type="button" id="clicky" value="Show Form" -->
</body>
</html>