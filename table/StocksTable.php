<?php // StocksTable.php


class StocksTable {
	
	public function makeStocksTable($owner,$stocks){
		//echo "<p>we're in makeStocksTable</p>";
		$result = "";
		$result .= "<script src=\"js/stocksTable.js\"></script>";
	
		// This table is holds all the stock purchases and buttons
		$result .= "<table id=\"stockstable\" class=\"center\">";
		$result .= "<caption> <!-- appears after the table, not above it -->";
		$result .= "I'm using the caption tag here...  Giggidy.";
		$result .= "</caption>";
		$result .= "<thead>";
			$result .= "<tr>";
				$result .= "	<input type=\"hidden\" name=\"owner\" id=\"owner\" value=\"". $owner ."\" />";
				$result .= "<th><button id=\"removie\">Remove</button></th>"; //                    The Remove BUTTON
				$result .= "<th colspan=\"3\">";
				$result .= "	<!--  this just triggers the Add form to pop up.  Form has its own buttons -->";
				$result .= "	<input  type=\"submit\" id=\"addy\" value=\"Add a stock purchase\">"; //   the Add BUTTON
				$result .= "</th>";
				$result .= "<th colspan=\"3\">Activity Today</th>";
				$result .= "<th colspan=\"2\">Change Since Purchase</th>";
				$result .= "<th colspan=\"3\">Cost Basis -- including fee</th>";
				$result .= "<th colspan=\"3\">Purchas Details</th>";
			$result .= "</tr>";
			$result .= "<tr>";
			$result .= "<th><input type=\"checkbox\" name=\"remove[]\" id=\"selectAll\" value=\"all\"></th>"; // CHECKBOX (main one)
			$result .= "<th>Edit</th>";
			$result .= "<th>Name</th>";
			$result .= "<th>Symbol</th>";
			$result .= "<th>Price</th>";
			$result .= "<th>$ change</th>";
			$result .= "<th>% change</th>";
			$result .= "<th>Quantity</th>";
			$result .= "<th>Purch. Price</th>";
			$result .= "<th>Total Cost</th>";
			$result .= "<th>Total $ Change</th>";
			$result .= "<th>Total % Change</th>";
			$result .= "<th>Fee</th>";
			$result .= "<th>Account</th>";
			$result .= "<th>Purchase Date</th>";
			$result .= "</tr>";			
		$result .= "</thead>";
		$result .= "<tbody>";
			// Because this is a SPA using JavaScript, this call will only happen once: 
			// only when the page is loaded, so there's no danger of 
			// having multiple bodies happening, because JavaScript will simply replace everything
			// WITHIN the tbody (referencing the id), but not the tbody itself............
			$result .= $this->makeStocksTBODY($stocks);
		$result .= "</tbody>";
		//$result .= "</form>";
		$result .= "</table>";
		
		
		
		// this div holds the little "Add a stock purchase" pop-up form (as a table)
		$result .= "<div id=\"the_add_form\"  title=\"Add a purchase\">";
		$result .= "<!-- Pop-up form hidden by JavaScript until button is clicked -->";
		//<!-- I got it here: https://github.com/apanzerj/Former-For-Zendesk/blob/Lesson-2-Branch/former.html#L14
		//and here: http://jsfiddle.net/eARdt/170/ and someplace at zendesk (google it) -->
		$result .= "	<form id=\"addsymbol\" action=\"addPurch.php\" method=\"POST\" name=\"addstock\" enctype=\"multipart/form-data\">";
		$result .= "	<!-- Note To Self: they don't just go via POST.  They have to be 'hand loaded' into the string";
		$result .= "	that looks like a GET string, down in the JavaScript code.  See the \"click: function()\" part of that mess. -->";
		
		// this one's prolly redundant because it's in the main table, too.
		$result .= "	<input type=\"hidden\" name=\"owner\" id=\"owner\" value=\"" . $owner . "\" />";
		
		$result .= "		<table class=\"popuptable\">";
		$result .= "		<thead>";
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
		$result .= "		<label for=\"fee\">Fee: </label>";
		$result .= "		</td><td>";
		$result .= "		<input type=\"text\" name=\"fee\" id=\"fee\">";
		$result .= "		</td>";
		$result .= "		</tr>";
		$result .= "		<tr><td class=\"right\">";
		$result .= "		<label for=\"datepicker\">Date Purchased: </label>";
		$result .= "		</td><td>";
		$result .= "		<input type=\"text\" name=\"datepicker\" id=\"datepicker\">";
		$result .= "		</td>";
		$result .= "		</tr>";
		$result .= "		<tr><td class=\"right\">";
		$result .= "		<label for=\"account\">Which account: </label>";
		$result .= "		</td><td>";
		$result .= "		<select name=\"account\" id=\"account\">";
		$result .= "		<option value=\"FidelityIndividual\">Fidelity Individual</option>";
		$result .= "		<option value=\"FidelitySEP\">Fidelity SEP</option>";
		$result .= "		<option value=\"FidelityRoth\">Fidelity Roth</option>";
		$result .= "		<option value=\"ScottradeIRA\">Scottrade IRA</option>";
		$result .= "		</select>";
		$result .= "		</td>";
		$result .= "		</tr>";
		$result .= "		</table>";
		$result .= "	</form>";
		$result .= "</div>";
		
		
		//NOTE: these two tables are very similar, so, this is a good example of how the id's have to be different. 
		// (html id's have to be unique on a page)
		
		// this div holds the little "Edit a stock purchase" pop-up form (as a table)
		$result .= "<div id=\"the_edit_form\"  title=\"Edit a purchase\">";
		$result .= "<!-- Pop-up form hidden by JavaScript until button is clicked -->";
		//<!-- I got it here: https://github.com/apanzerj/Former-For-Zendesk/blob/Lesson-2-Branch/former.html#L14
		//and here: http://jsfiddle.net/eARdt/170/ and someplace at zendesk (google it) -->
		$result .= "	<form id=\"editsymbol\" action=\"editPurch.php\" method=\"POST\" name=\"editstock\" enctype=\"multipart/form-data\">";
		$result .= "	<!-- Note To Self: they don't just go via POST.  They have to be 'hand loaded' into the string";
		$result .= "	that looks like a GET string, down in the JavaScript code.  See the \"click: function()\" part of that mess. -->";

		// this one's prolly redundant because it's in the main table, too.
		$result .= "	<input type=\"hidden\" name=\"owner\" id=\"editowner\" value=\"" . $owner . "\" />";
		$result .= "	<input type=\"hidden\" name=\"id\" id=\"editid\" value=\"\" />";

		$result .= "		<table class=\"popuptable\">";
		
		$result .= "		<thead>";		
		$result .= "		<tr>";
		$result .= "			<th colspan=\"2\">";
		$result .= "				<h3 id=\"companyname\"> </h3>";
		$result .= "			</th>";
		$result .= "		</tr>";		
		$result .= "		</thead>";
		
		$result .= "		<tbody>";		
		$result .= "		<tr>";		
		$result .= "			<td class=\"right\">";
		$result .= "				<label for=\"symbol\">Symbol:</label>";
		$result .= "			</td>";
		$result .= "			<td>";
		$result .= "				<input type=\"text\" name=\"symbol\" id=\"editsymbol\" value=\"\">";
		$result .= "			</td>";
		$result .= "		</tr>";
		
		$result .= "		<tr>";
		$result .= "			<td class=\"right\">";
		$result .= "				<!--  for=\"...\" Specifies which form element a label is bound to -->";
		$result .= "				<label for=\"quant\">Quantity:</label>";
		$result .= "			</td>";
		$result .= "			<td>";
		$result .= "				<input type=\"text\" name=\"quant\" id=\"editquant\" value=\"\">";
		$result .= "			</td>";
		$result .= "		</tr>";
		$result .= "		<tr>";
		$result .= "			<td>";
		$result .= "				<label for=\"price\">Price for each: </label>";
		$result .= "			</td>";
		$result .= "			<td>";
		$result .= "				<input type=\"text\" name=\"price\" id=\"editprice\" value=\"\">";
		$result .= "			</td>";
		$result .= "		</tr>";
		
		$result .= "		<tr>";
		$result .= "			<td class=\"right\">";
		$result .= "			<label for=\"fee\">Fee: </label>";
		$result .= "			</td>";
		$result .= "			<td>";
		$result .= "				<input type=\"text\" name=\"fee\" id=\"editfee\" value=\"\">";
		$result .= "			</td>";
		$result .= "		</tr>";
		
		$result .= "		<tr>";
		$result .= "			<td class=\"right\">";
		$result .= "				<label for=\"datepicker\">Date Purchased: </label>";
		$result .= "			</td>";
		$result .= "			<td>";
		$result .= "				<input type=\"text\" name=\"datepicker\" id=\"editdatepicker\" value=\"\">";
		$result .= "			</td>";
		$result .= "		</tr>";
		
		$result .= "		<tr>";
		$result .= "			<td class=\"right\">";
		$result .= "				<label for=\"account\">Which account: </label>";
		$result .= "			</td>";
		$result .= " 			<td>";
		$result .= "				<select name=\"account\" id=\"editaccount\">";
		$result .= "				<option value=\"FidelityIndividual\">Fidelity Individual</option>";
		$result .= "				<option value=\"FidelitySEP\">Fidelity SEP</option>";
		$result .= "				<option value=\"FidelityRoth\">Fidelity Roth</option>";
		$result .= "				<option value=\"ScottradeIRA\">Scottrade IRA</option>";
		$result .= "				</select>";
		$result .= "			</td>";
		$result .= "		</tr>";
		$result .= "		</tbody>";
		
		$result .= "		</table>";
		$result .= "	</form>";
		$result .= "</div>";		
		
		
		return $result;
	} // end  makeStocksTable($owner,$stocks)
	

	public function makeStocksTBODY($stocks){
		
		$neg = "neg";
		$pos = "pos";
		
		foreach ($stocks as $s){
			$totalCost = $s['purchasequantity'] * $s['purchaseprice'] + $s['purchasefee'];
			$totalCurrentValue =  $s['purchasequantity'] * $s['LastTradePriceOnly'] - $s['purchasefee'];
			$dollarchange = $s['LastTradePriceOnly'] - $s['purchaseprice'];
			$totalChangeDollar = $totalCurrentValue - $totalCost;
			$totalChangePercent = $totalChangeDollar / $totalCost * 100;
			$percentchangetoday = $s['Change'] / $s['LastTradePriceOnly'] * 100;
						
			$result .= "<tr>";
			
			$result .= "<td>";
			$result .= "<input type=\"checkbox\" name=\"remove[]\" value=\"" . (string)$s['_id'] . "\">"; // CHECKBOX
			$result .= "</td>";
			
			$result .= "<td>";			
			$result .= "	<input type=\"image\" name=\"editfields" . (string)$s['_id'] . "\" id=\"edit" . (string)$s['_id'] . "\""; // Edit button
			$result .= "	src=\"images/gtk_edit.png\"";
			$result .= "	alt=\"Edit this row\"";
			$result .= "	value=\"" . (string)$s['_id'] . "\">";  //onclick=\"edit();\" 
			// opens a pop-up form as an overlay (id: #the_edit_form). 
			// This is inline because each edit button needs its own unique id.
			$result .= "	<script>";
			//POZOR!!: if you add this comment to $result, it will COMMENT OUT THE WHOLE CONTENTS OF THE SCRIPT TAGS!!!
			// adding multiple $(documents).ready()s is not a problem. All will be executed on the ready event.";
			$result .= "		$(document).ready(function() {";
			$result .= "		$(\"#edit" . (string)$s['_id'] . "\").click(function(e){";
			//$result .= "			alert(\"in the <script> function\");";
			//$result .= "			alert($(\"input[name='editfields" . (string)$s['_id'] . "']\").val());";
			//$result .= "			alert(\"" . $s['Name'] . "\");";
			//POZOR!!: if you add this comment to $result, it will COMMENT OUT THE WHOLE CONTENTS OF THE SCRIPT TAGS!!!
			// this group is to populate 'existing' fields into the form.";
			$result .= "			$(\"#companyname\").text(\"" 	. $s['Name'] . "\");";
			$result .= "			$(\"#editsymbol\").val(\"" 		. $s['symbol'] . "\");";
			$result .= "			$(\"#editquant\").val(\"" 		. $s['purchasequantity'] . "\");";
			$result .= "			$(\"#editprice\").val(\"" 		. $s['purchaseprice'] . "\");";
			$result .= "			$(\"#editfee\").val(\"" 		. $s['purchasefee'] . "\");";
			$result .= "			$(\"#editdatepicker\").val(\"" 	. $s['purchasedate'] . "\");";
			$result .= "			$(\"#editaccount\").val(\"" 	. $s['account'] . "\");";
			$result .= "			$(\"#editid\").val(\"" 	. (string)$s['_id'] . "\");";
			$result .= "			$(\"#editowner\").text(\"" 	. $owner . "\");";
//			$result .= "			e.preventDefault();";
			$result .= "			$('#the_edit_form')";
			$result .= "				.dialog('open');";
			$result .= "		});";
			$result .= "		});";
			$result .= "	</script>";
			$result .= "</td>";
			
			$result .= "<td class=\"left\">". $s['Name'] . "</td>";
			$result .= "<td class=\"left\">". $s['symbol'] . "</td>";
			$result .= "<td class=\"right\">$". $s['LastTradePriceOnly'] . "</td>";
			$result .= "<td class=\"";
			$result .= ($s['Change']>0) ? $pos : $neg;
			$result .= "\">" . $s['Change'] . "</td>";
			$result .= "<td  class=\"right ";
			$result .= ($percentchangetoday>0) ? $pos : $neg;
			$result .= "\">". number_format($percentchangetoday, 2, '.', ',') . "%</td>";
			$result .= "<td>". $s['purchasequantity'] . "</td>";
			$result .= "<td class=\"right\">". $s['purchaseprice'] . "</td>";
			$result .= "<td class=\"right\">". number_format($totalCost, 2, '.', ',') . "</td>";
			$result .= "<td  class=\"right ";
			$result .= ($totalChangeDollar>0) ? $pos : $neg;
			$result .= "\">$". number_format($totalChangeDollar, 2, '.', ',') . "</td>";
			$result .= "<td  class=\"right ";
			$result .= ($totalChangePercent>0) ? $pos : $neg;
			$result .= "\">". number_format($totalChangePercent, 2, '.', ',') . "%</td>";
			$result .= "<td class=\"right\">". number_format($s['purchasefee'], 2, '.', ',')  . "</td>";
			$result .= "<td class=\"left\">". $s['account'] . "</td>";
			$result .= "<td class=\"left\">". $s['purchasedate'] . "</td>";	
			
			$result .= "</tr>";
		}
		
		//$result .= "<script src=\"js/tablebuttons.js\"></script>";
	
		/*
		 [_id] => MongoId Object [$id] => 557e57b35490c4d605b85e02
		[symbol] => YHOO
		[AverageDailyVolume] => 14258000
		[Change] => -0.410
		[DaysLow] => 40.455
		[DaysHigh] => 41.110
		[YearLow] => 32.930
		[YearHigh] => 52.620
		[MarketCapitalization] => 38.03B
		[LastTradePriceOnly] => 40.525
		[DaysRange] => 40.455 - 41.110
		[Name] => Yahoo! Inc.
		[Symbol] => YHOO
		[Volume] => 9232651
		[StockExchange] => NMS
		[purchasedate] => 7/8/69
		[purchasequantity] => 156
		[purchaseprice] => 127.34
		[account] => Fidelity IRA
		[purchasefee] => 7
		[owner] => me
		*/
		return $result;
	} // end makeStocksTBODY($stocks)

} // end class StocksTable 


?>
