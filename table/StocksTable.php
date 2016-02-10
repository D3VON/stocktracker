<?php // StocksTable.php

/* This is the contraption that builds the stock table */


class StocksTable {
	
	public function makeStocksTable($owner,$stocks){
		//echo "<p>we're in makeStocksTable</p>";
		$result = "";
		$result .= "<script src=\"js/stocksTable.js\"></script>";
	
		// This table is holds all the stock purchases and buttons
		$result .= "<table id=\"stockstable\" class=\"center\">";
		$result .= "<caption> <!-- appears after the table, not above it -->";
		$result .= "There is no caption.  Giggidy.";
		$result .= "</caption>";
		$result .= "<thead>";
			$result .= "<tr>";
				$result .= "	<input type=\"hidden\" name=\"owner\" id=\"owner\" value=\"". $owner ."\" />";
				$result .= "<th colspan=\"2\"><button id=\"removie\">Remove</button></th>"; //                    The Remove BUTTON
				$result .= "<th colspan=\"2\">";
				$result .= "	<!--  this just triggers the Add form to pop up.  Form has its own buttons -->";
				$result .= "	<input  type=\"submit\" id=\"addy\" value=\"Add a stock purchase\">"; //   the Add BUTTON
				$result .= "</th>";
				$result .= "<th colspan=\"3\">Activity Today</th>";
				$result .= "<th colspan=\"2\">Change Since Purchase</th>";
				$result .= "<th colspan=\"1\">Current Value</th>";
				$result .= "<th colspan=\"3\">Cost Basis -- including fee</th>";
				$result .= "<th colspan=\"3\">Purchas Details</th>";
				$result .= "<th>52 week Low & High</th>";
			$result .= "</tr>";
			$result .= "<tr class='titles'>";
			$result .= "<th><input type=\"checkbox\" name=\"remove[]\" id=\"selectAll\" value=\"all\"></th>"; // CHECKBOX (main one)
			$result .= "<th>Edit</th>";
			
			//TRYING TO FIGURE OUT HOW TO SORT COLUMNS, ASCENDING / DESCENDING...
			//<a id="sort_ascend" href="good/1.html">Name</a>
			//<a class="sort_descend" href="good/2.html">Click Here</a>
			//$result .= "<th>";
			//$result .= "<input type=\"hidden\" name=\"sort_flag\" id=\"sort_flag\" value=\"1\" />";
			//$result .= "<a href=\"javascript:sortColumn('name_ascend');\">Name</a>";
			//$result .= "</th>";
			
			// COLUMNS TO CLICK FOR SORTING
			$result .= "<th class=\"sortByColumn asc\">Name</th>";
			$result .= "<th class=\"sortByColumn\">Symbol</th>";
			$result .= "<th>Price</th>";
			$result .= "<th class=\"sortByColumn\">$ change</th>";
			$result .= "<th class=\"sortByColumn\">% change</th>";
			$result .= "<th class=\"sortByColumn\">Total $ Change</th>";
			$result .= "<th class=\"sortByColumn\">Total % Change</th>";
			$result .= "<th class=\"sortByColumn\">Total Value</th>";
			$result .= "<th class=\"sortByColumn\">Total Cost</th>";
			$result .= "<th>Quantity</th>";
			$result .= "<th>Purch. Price</th>";
			$result .= "<th>Fee</th>";
			$result .= "<th class=\"sortByColumn\">Account</th>";
			$result .= "<th class=\"sortByColumn\">Purchase Date</th>";
			$result .= "<th></th>";
			
			
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
				
		$agrigateChangeDollars = 0;
		$agrigateCostDollars = 0;
		$agrigateCurrentValue = 0;
		$neg = "neg";
		$pos = "pos";
		$result = "";
		foreach ($stocks as $s){
			$totalCost = $s['purchasequantity'] * $s['purchaseprice'] + $s['purchasefee'];
			$agrigateCostDollars += $totalCost;
			$totalCurrentValue =  $s['purchasequantity'] * $s['LastTradePriceOnly'] - $s['purchasefee'];
			$agrigateCurrentValue += $totalCurrentValue;
			//$dollarchange = $s['LastTradePriceOnly'] - $s['purchaseprice'];
			$totalChangeDollar = $totalCurrentValue - $totalCost;
			$agrigateChangeDollars += $totalChangeDollar;
			
			$totalChangePercent = $totalCost != 0 ? $totalChangeDollar / $totalCost * 100 : 0;
			$percentchangetoday = $s['LastTradePriceOnly'] != 0 ? $s['Change'] / $s['LastTradePriceOnly'] * 100 : 0;
						
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
			$result .= "			$(\"#editowner\").text(\"" 	. $s['owner'] . "\");";
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
			$result .= "\">$" . $s['Change'] . "</td>";
			$result .= "<td  class=\"right ";
			$result .= ($percentchangetoday>0) ? $pos : $neg;
			$result .= "\">". number_format($percentchangetoday, 2, '.', ',') . "%</td>";
			$result .= "<td  class=\"right ";
			$result .= ($totalChangeDollar>0) ? $pos : $neg;
			$result .= "\">$". number_format($totalChangeDollar, 2, '.', ',') . "</td>";
			$result .= "<td  class=\"right ";
			$result .= ($totalChangePercent>0) ? $pos : $neg;
			$result .= "\">". number_format($totalChangePercent, 2, '.', ',') . "%</td>";
			$result .= "<td class=\"right\">$". number_format($totalCurrentValue, 2, '.', ',') . "</td>";
			$result .= "<td class=\"right\">$". number_format($totalCost, 2, '.', ',') . "</td>";
			$result .= "<td  class=\"right\">". $s['purchasequantity'] . "</td>";
			$result .= "<td class=\"right\">". $s['purchaseprice'] . "</td>";
			$result .= "<td class=\"right\">". number_format($s['purchasefee'], 2, '.', ',')  . "</td>";
			$result .= "<td class=\"left\">". $s['account'] . "</td>";
			$result .= "<td class=\"left\">". $s['purchasedate'] . "</td>";


			/***************************************************************************************
			 ***************************************************************************************
			 * This block is INLINE D3.js.  It is originally a graph I found called "threshold key"
			 * but I've repurposed it to show 52 week high, low, and the stock's gain or loss
			 *
			 * NOTE: very shaggy hacking: Instead of declaring, e.g., "var x" many times
			 * (this code is called many times in a loop) causing it to be declared many times
			 * in the same script, I'm 'augmenting' the name, e.g., "var x", with the database id
			 * of each stock thusly: _" . (string)$s['_id'] . " so as to make each declaration UNIQUE!
			 ***************************************************************************************
			 ***************************************************************************************/
			// this boolean does 2 things: controls whether graph is red or green,
			// and controls order in graph of purchase price & current price
			$redboolean = (($s['LastTradePriceOnly'] - $s['purchaseprice']) < 0) ? TRUE : FALSE;
			$graphcolor = ($redboolean) ? "red" : "green";

			// guard against stocks purchased more than 52 weeks ago at a lower or higher price
			$purchPriceTooLow = ($s['YearLow'] > $s['purchaseprice']) ? TRUE : FALSE;
			$purchPriceTooHigh = ($s['YearHigh'] < $s['purchaseprice']) ? TRUE : FALSE;
			//Then we can only have two colored ranges instead of three

			// ONLY SHOW 52 WEEK DATA, not longer ago than that (may have gained or lost more, but only show 1 year)

			$redboolean = (($s['LastTradePriceOnly'] - $s['purchaseprice']) < 0) ? TRUE : FALSE;


			$result .= "<td class=\"left\" id=\"graph_" . (string)$s['_id'] . "\">";

			$result .= "<script>";

			// handle possible overflow of 52 week values
			if($purchPriceTooLow){
				$pp = $s['YearLow'];
			}elseif($purchPriceTooHigh){
				$pp = $s['YearHigh'];
			}else{
				$pp = $s['purchaseprice'];
			}

			// this thing holds the 'ticks' -- the axis values
    		$result .= "var threshold_" . (string)$s['_id'] . " = d3.scale.threshold().domain([".$s['YearLow'];
			if($redboolean){// will be red, showing loss
				$result .= ",". $s['LastTradePriceOnly'] . "," . $pp;
			}else{//willbe green showing gain
				$result .= ",". $pp . "," . $s['LastTradePriceOnly'];
			}
			$result .= "," . $s['YearHigh']. "])";
						//orig:  .range(["#ffffff","#ffffff", "#a0b28f", "#d8b8b3", "#b45554", "#760000","#ffffff"]);
			$result .= ".range([\"#ffffff\",\"#ffffff\", \"" . $graphcolor . "\", \"#ffffff\"]);";

			// A position encoding for the key only. --that is, for the gain/loss part of the graph
			$result .= "var x_" . (string)$s['_id'] . " = d3.scale.linear()"; // .domain([0, 1]) // original was percentages between 0 and 1
			$result .= ".domain([".$s['YearLow'].",". $s['YearHigh']."]).range([0, 200]);"; // range is the width of the graph within its container (see 'width' var)

			$result .= "var xAxis_" . (string)$s['_id'] . " = d3.svg.axis().scale(x_" . (string)$s['_id'] . ").orient(\"bottom\").tickSize(9).tickValues(threshold_" . (string)$s['_id'] . ".domain())";
			$result .= ".tickFormat(d3.format(\"$,.2f\"));";
			// 'width' and 'height' are size of 'container' of the graph
			$result .= "var svg_" . (string)$s['_id'] . " = d3.select(\"#graph_" . (string)$s['_id'] . "\").append(\"svg\").attr(\"width\", 250).attr(\"height\", 20);";

			$result .= "var g_" . (string)$s['_id'] . " = svg_" . (string)$s['_id'] . ".append(\"g\").attr(\"class\", \"key\").attr(\"transform\", \"translate(25, 1)\");"; // 25px from left, 0 px from the bottom

			$result .= "g_" . (string)$s['_id'] . ".selectAll(\"rect\").data(threshold_" . (string)$s['_id'] . ".range().map(function(color) {";
			$result .= "		var d_" . (string)$s['_id'] . " = threshold_" . (string)$s['_id'] . ".invertExtent(color);";
			$result .= "		if (d_" . (string)$s['_id'] . "[0] == null) d_" . (string)$s['_id'] . "[0] = x_" . (string)$s['_id'] . ".domain()[0];";
			$result .= "		if (d_" . (string)$s['_id'] . "[1] == null) d_" . (string)$s['_id'] . "[1] = x_" . (string)$s['_id'] . ".domain()[1];";
			$result .= "		return d_" . (string)$s['_id'] . ";";
			$result .= "	}))";
			$result .= ".enter().append(\"rect\")";
			$result .= ".attr(\"height\", 7)"; // this is the height of the graph's horizontal bar
			$result .= ".attr(\"x\", function(d) { return x_" . (string)$s['_id'] . "(d[0]); })";
			$result .= ".attr(\"width\", function(d) { return x_" . (string)$s['_id'] . "(d[1]) - x_" . (string)$s['_id'] . "(d[0]); })";
			//.style(\"stroke\", \"#000\")"; // makes just the middle block outlined with a line
			$result .= ".style(\"fill\", function(d) { return threshold_" . (string)$s['_id'] . "(d[0]); });";

			$result .= "g_" . (string)$s['_id'] . ".call(xAxis_" . (string)$s['_id'] . ");";  // orig: .append("text")
							// orig: .attr("class", "caption")
							// orig: .attr("y", 0)
							// orig: .attr("y", -6)
							// orig: .text("Percentage of stops that involved force")
							// orig: ;
			//top line
			$result .= "var borderPathTop_" . (string)$s['_id'] . " = svg_" . (string)$s['_id'] . ".append(\"rect\")";
			$result .= "			.attr(\"x\", 25)"; // 25px from the absolute left
			$result .= "			.attr(\"y\", 8)"; // 0px from the absolute top of where the graph is drawn
			$result .= "			.attr(\"height\", 1)";
			$result .= "			.attr(\"width\", 201);";
			//bottom line
			$result .= "var borderPathBottom_" . (string)$s['_id'] . " = svg_" . (string)$s['_id'] . ".append(\"rect\")";
			$result .= "			.attr(\"x\", 25)";
			$result .= "			.attr(\"y\", 1)";
			$result .= "			.attr(\"height\", 1)";
			$result .= "			.attr(\"width\", 201);";

			$result .= "</script>";


			/*****************************************************************************************
			 * End of D3.js graph in this table cell
			 */

			$result .= "</tr>";
		}
		
		/* build last row, which shows agrigate values */
		$result .= "<tr>";
		$result .= "<td></td>";
		$result .= "<td></td>";
		$result .= "<td></td>";
		$result .= "<td></td>";
		$result .= "<td></td>";
		$result .= "<td></td>";
		$result .= "<td></td>";
		$result .= "<td class=\"right\">$" . number_format($agrigateChangeDollars, 2, '.', ',') . "</td>";
		$result .= "<td></td>";
		$result .= "<td class=\"right\">$" . number_format($agrigateCurrentValue, 2, '.', ',') . "</td>";
		$result .= "<td class=\"right\">$" . number_format($agrigateCostDollars, 2, '.', ',') . "</td>";
		$result .= "<td></td>";
		$result .= "<td></td>";
		$result .= "<td></td>";
		$result .= "<td></td>";
		$result .= "<td></td>";			
		$result .= "</tr>";
		
		
		
		
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
