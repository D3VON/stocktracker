<!doctype html> 
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Rickshaw / D3 graphs </title>
		<link rel="stylesheet" href="css/reset.css">
		<link type="text/css" rel="stylesheet" href="css/stocks.css">
		
		<link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css">
		<!--script src="js/bootstrap.min.js"></script -->	
		<style type="text/css">
			.bullbear {
			    display: block;
			    margin-left: auto;
			    margin-right: auto; }
			.centertitle {
			    display: block;
			    margin-left: auto;
			    margin-right: auto; }
			.centertable {
			    display: block;
			    margin-left: auto;
			    margin-right: auto; }
			#box_form > *{
			    font-size:12px;
			    margin-bottom:15px;
			}
			#box_form > p > label{
			    display:block;
			    font-size:12px;
			}
			#box_form > p > input, #box_form > p > textarea{
			        width:100px;
			}    
		</style>

	</head>
	<body>

		<h1 class="centertitle">Stock Tracker -- "My Account"</h1>
		<!-- h2>June 22, 2014</h2 -->
		<p><img  class="bullbear" src="images/bullbear.gif" alt="bull and bear"></p>
				
		<form id="getOwner" action=""> 
			Owner name: <input type="text" name="owner">
			<input id="theOwnerButton"  type="submit" value="Specify Owner">
		</form>
		
<?php		

	require_once('../MongoToYQL_Adapter.php');
	
	function makeStocksTable($stocks, $owner){
		$result = "";
		
		$result .= "<div id=\"box_form\"  title=\"Add a purchase\">";
		$result .= "<!-- Pop-up form hidden by JavaScript until button is clicked -->";
		//<!-- I got it here: https://github.com/apanzerj/Former-For-Zendesk/blob/Lesson-2-Branch/former.html#L14
		//and here: http://jsfiddle.net/eARdt/170/ and someplace at zendesk (google it) -->
		$result .= "<form id=\"addsymbol\" action=\"addPurch.php\" method=\"POST\" name=\"addstock\" enctype=\"multipart/form-data\">";
		$result .= "<!-- Note To Self: they don't just go via POST.  They have to be 'hand loaded' into the string";
		$result .= "that looks like a GET string, down in the JavaScript code.  See the \"click: function()\" part of that mess. -->";
		$result .= "		<input type=\"hidden\" name=\"owner\" id=\"owner\" value=\"me\" />";
		
		$result .= "		<table class=\"popuptable\">";
		$result .= "		<tr><td class=\"right\">";
		$result .= "		<label for=\"symbol\">Symbol:</label>";
		$result .= "		</td><td>";
		$result .= "		<input type=\"text\" name=\"symbol\" id=\"symbol\">";
		$result .= "		</td>";
		$result .= "		</tr>";
		$result .= "		<tr><td class=\"right\">";
		$result .= "		<!--  for=\"...\" Specifies which form element a label is bound to -->";
		$result .= "		<label for=\"quant\">Quantity:</label>";
		$result .= "		</td><td>";
		$result .= "		<input type=\"text\" name=\"quant\" id=\"quant\">";
		$result .= "		</td>";
		$result .= "		</tr>";
		$result .= "		<tr><td class=\"right\">";
		$result .= "		<label for=\"price\">Price for each: </label>";
		$result .= "		</td><td>";
		$result .= "		<input type=\"text\" name=\"price\" id=\"price\">";
		$result .= "		</td>";
		$result .= "		</tr>";
		$result .= "		<tr><td class=\"right\">";
		$result .= "		<label for=\"datepicker\">Date Purchased: </label>";
		$result .= "		</td><td>";
		$result .= "		<input type=\"text\" name=\"datepicker\" id=\"datepicker\">";
		$result .= "		<!-- had real trouble getting data to POST, just made all names,ids === and it worked -->";
		$result .= "		</td>";
		$result .= "		</tr>";
		$result .= "		<tr><td class=\"right\">";
		$result .= "		<label for=\"fee\">Fee: </label>";
		$result .= "		</td><td>";
		$result .= "		<input type=\"text\" name=\"fee\" id=\"fee\">";
		$result .= "		</td>";
		$result .= "		</tr>";
		$result .= "		<tr><td class=\"right\">";
		$result .= "		<label for=\"account\">Which account: </label>";
		$result .= "		</td><td>";
		$result .= "		<select name=\"account\" id=\"account\">";
		$result .= "		<option value=\"note\">must fix fee functionality</option>";
		$result .= "		<option value=\"FidelityIndividual\">Fidelity Individual</option>";
		$result .= "		<option value=\"FidelitySEP\">Fidelity SEP</option>";
		$result .= "		<option value=\"FidelityRoth\">Fidelity Roth</option>";
		$result .= "		<option value=\"ScottradeIRA\">Scottrade IRA</option>";
		$result .= "		</select>";
		$result .= "		</td>";
		$result .= "		</tr>";
		$result .= "		</table>";
		$result .= "		</form>";
		$result .= "		</div>";
	
		$result .= "<table class=\"center\">";
		$result .= "<caption> <!-- appears after the table, not above it -->";
		$result .= "I'm using the caption tag here...  Giggidy.";
		$result .= "</caption>";
		$result .= "<tr>";
		$result .= "<th colspan=\"2\">";
		$result .= "	<!--  this just triggers form to come up.  Form has its own buttons -->";
		$result .= "	<input  type=\"submit\" id=\"addy\" value=\"Add a stock purchase\">";
		$result .= "</th>";
		$result .= "<th colspan=\"3\">Activity Today</th>";
		$result .= "<th colspan=\"2\">Change Since Purchase</th>";
		$result .= "<th colspan=\"3\">Cost Basis</th>";
		$result .= "<th colspan=\"3\">Purchas Details</th>";
		$result .= "<form action=\"removePurch.php\" id=\"removie\" method=\"post\" enctype=\"multipart/form-data\">"; // for checkboxes
		$result .= "<input type=\"hidden\" name=\"owner\" id=\"owner\" value=\"me\" />";
		$result .= "<th><button id=\"remove_button\">Remove</button></th>";
		//$result .= "<th><input type=\"submit\" value=\"Remove\" /></th>"; // BUTTON
		$result .= "</tr>";
		$result .= "<tr>";
		$result .= "<th>Name</th>";
		$result .= "<th>Symbol</th>";
		$result .= "<th>Price</th>";
		$result .= "<th>$ change</th>";
		$result .= "<th>% change</th>";
		$result .= "<th>Quantity</th>";
		$result .= "<th>Purchase Price</th>";
		$result .= "<th>Total Cost</th>";
		$result .= "<th>Total % Change</th>";
		$result .= "<th>Total $ Change</th>";
		$result .= "<th>Fee</th>";
		$result .= "<th>Account</th>";
		$result .= "<th>Purchase Date</th>";
		$result .= "<th><input type=\"checkbox\" name=\"remove[]\" id=\"selectAll\" value=\"all\"></th>"; // CHECKBOX (main one)
		$result .= "</tr>";
	
		foreach ($stocks as $s){
			$cost = $s['purchasequantity'] * $s['purchaseprice'] + $s['purchasefee'];
			$dollarchange = $s['LastTradePriceOnly'] - $s['purchaseprice'];
			$percentchangetotal = $dollarchange / $s['purchaseprice'] * 100;
			$percentchangetoday = $s['Change'] / $s['LastTradePriceOnly'] * 100;
			$result .= "<tr>";
			$result .= "<td>". $s['Name'] . "</td>";
			$result .= "<td>". $s['symbol'] . "</td>";
			$result .= "<td>". $s['LastTradePriceOnly'] . "</td>";
			$result .= "<td>". $s['Change'] . "</td>";
			$result .= "<td>". number_format($percentchangetoday, 2, '.', ',') . "</td>";
			$result .= "<td>". number_format($percentchangetotal, 2, '.', ',') . "%</td>";
			$result .= "<td>$". number_format($dollarchange, 2, '.', ',') . "</td>";
			$result .= "<td>". $s['purchasequantity'] . "</td>";
			$result .= "<td>". $s['purchaseprice'] . "</td>";
			$result .= "<td>". number_format($cost, 2, '.', ',') . "</td>";
			$result .= "<td>". number_format($s['purchasefee'], 2, '.', ',')  . "</td>";
			$result .= "<td>". $s['account'] . "</td>";
			$result .= "<td>". $s['purchasedate'] . "</td>";
	
			$result .= "<td>";
			$result .= "<input type=\"checkbox\" name=\"remove[]\" value=\"" . (string)$s['_id'] . "\">"; // CHECKBOX
			$result .= "</td>";
			$result .= "</tr>";
		}
		$result .= "</table>";
		$result .= "</form>";

		return $result;
	} // end function makeStocksTable($stocks)
	
	try{ 
		$mongo = new MongoToYQL_Adapter;
	} catch (Exception $e) {
    	echo 'Caught exception: ',  $e->getMessage(), "<br>";
	}	
	
	echo "<div id=\"thetable\" class=\"centertable\">";
	$owner = "me";
	$stocks = $mongo->getAllStocksByOwner($owner);
	//echo $doTable->makeStocksTable($stocks,$owner);
	echo makeStocksTable($stocks,$owner);
	echo "</div>";
	

?>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/jquery-ui.min.js"></script>
		<script src="js/stocksApp.js"></script>
	
</body>
</html>