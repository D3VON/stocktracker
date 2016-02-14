<?php

/**
 * Created by PhpStorm.
 * User: devonmcb
 * Date: 2/13/16
 * Time: 4:26 PM
 *
 * Using this as a place within the IDE to fix another php class before clobbering the old one with 'improved' stuff.
 */
class scratchtemp
{
    // This table is holds all the stock purchases and buttons

    public function makeStocksTable($owner,$stocks){

        $starttable_and_head = <<<THEAD
        
        <script src="js/stocksTable.js"></script>

        <table id="stockstable" class="center">
            <caption>There is no caption.  Giggidy.</caption>
            <thead>
                <tr>
                    <input type="hidden" name="owner" id="owner" value="$owner" /><!--Just populates the owner field in this form -->
                    <th colspan="2">
                        <button id="removie">Remove</button>
                    </th>
                    <th colspan="2">
                        <!--  triggers the Add form to pop up. -->
                        <input  type="submit" id="addy" value="Add a stock purchase">
                    </th>
                    <th colspan="3">Activity Today</th>
                    <th colspan="2">Change Since Purchase</th>
                    <th colspan="1">Current Value</th>
                    <th colspan="3">Cost Basis -- including fee</th>
                    <th colspan="3">Purchas Details</th>
                    <th>52 week Low, High, Last</th>
                </tr>
                <tr class='titles'>
                    <th>
                        <!-- top checkbox to fill all the checkboxes -->
                        <input type="checkbox" name="remove[]" id="selectAll" value="all">
                    </th>
                    <th>Edit</th>
                    <!--
                        TRYING TO FIGURE OUT HOW TO SORT COLUMNS, ASCENDING / DESCENDING...
                        <a id="sort_ascend" href="good/1.html">Name</a>
                        <a class="sort_descend" href="good/2.html">Click Here</a>
                        <th>
                        <input type="hidden" name="sort_flag" id="sort_flag" value="1" />
                        <a href="javascript:sortColumn('name_ascend');">Name</a>
                        </th>
                     -->

                    <!-- COLUMNS TO CLICK FOR SORTING -->
                    <th class="sortByColumn asc">Name</th>
                    <th class="sortByColumn">Symbol</th>
                    <th>Price</th>
                    <th class="sortByColumn">$ change</th>
                    <th class="sortByColumn">% change</th>
                    <th class="sortByColumn">Total $ Change</th>
                    <th class="sortByColumn">Total % Change</th>
                    <th class="sortByColumn">Total Value</th>
                    <th class="sortByColumn">Total Cost</th>
                    <th>Quantity</th>
                    <th>Purch. Price</th>
                    <th>Fee</th>
                    <th class="sortByColumn">Account</th>
                    <th class="sortByColumn">Purchase Date</th>
                    <th></th>
                </tr>
            </thead>
<tbody>
// Because this is a SPA using JavaScript, this call will only happen once:
// only when the page is loaded, so there's no danger of
// having multiple bodies happening, because JavaScript will simply replace everything
// WITHIN the tbody (referencing the id), but not the tbody itself............
$result .= $this->makeStocksTBODY($stocks);
</tbody>
//</form>
</table>

THEAD;
        // End of the block that isolates the beginning of the


        $tbody = <<<TBOdddddddddddddddddddDY

// this div holds the little "Add a stock purchase" pop-up form (as a table)
<div id="the_add_form"  title="Add a purchase">
<!-- Pop-up form hidden by JavaScript until button is clicked -->
//<!-- I got it here: https://github.com/apanzerj/Former-For-Zendesk/blob/Lesson-2-Branch/former.html#L14
//and here: http://jsfiddle.net/eARdt/170/ and someplace at zendesk (google it) -->
	<form id="addsymbol" action="addPurch.php" method="POST" name="addstock" enctype="multipart/form-data">
	<!-- Note To Self: they don't just go via POST.  They have to be 'hand loaded' into the string
	that looks like a GET string, down in the JavaScript code.  See the "click: function()" part of that mess. -->

// this one's prolly redundant because it's in the main table, too.
	<input type="hidden" name="owner" id="owner" value="" . $owner . "" />

		<table class="popuptable">
		<thead>
		<tr><td class="right">
		<label for="symbol">Symbol:</label>
		</td><td>
		<input type="text" name="symbol" id="symbol">
		</td>
		</tr>
		<tr><td class="right">
		<!--  for="..." Specifies which form element a label is bound to -->
		<label for="quant">Quantity:</label>
		</td><td>
		<input type="text" name="quant" id="quant">
		</td>
		</tr>
		<tr><td class="right">
		<label for="price">Price for each: </label>
		</td><td>
		<input type="text" name="price" id="price">
		</td>
		</tr>
		<tr><td class="right">
		<label for="fee">Fee: </label>
		</td><td>
		<input type="text" name="fee" id="fee">
		</td>
		</tr>
		<tr><td class="right">
		<label for="datepicker">Date Purchased: </label>
		</td><td>
		<input type="text" name="datepicker" id="datepicker">
		</td>
		</tr>
		<tr><td class="right">
		<label for="account">Which account: </label>
		</td><td>
		<select name="account" id="account">
		<option value="FidelityIndividual">Fidelity Individual</option>
		<option value="FidelitySEP">Fidelity SEP</option>
		<option value="FidelityRoth">Fidelity Roth</option>
		<option value="ScottradeIRA">Scottrade IRA</option>
		</select>
		</td>
		</tr>
		</table>
	</form>
</div>


//NOTE: these two tables are very similar, so, this is a good example of how the id's have to be different.
// (html id's have to be unique on a page)

// this div holds the little "Edit a stock purchase" pop-up form (as a table)
<div id="the_edit_form"  title="Edit a purchase">
<!-- Pop-up form hidden by JavaScript until button is clicked -->
//<!-- I got it here: https://github.com/apanzerj/Former-For-Zendesk/blob/Lesson-2-Branch/former.html#L14
//and here: http://jsfiddle.net/eARdt/170/ and someplace at zendesk (google it) -->
	<form id="editsymbol" action="editPurch.php" method="POST" name="editstock" enctype="multipart/form-data">
	<!-- Note To Self: they don't just go via POST.  They have to be 'hand loaded' into the string
	that looks like a GET string, down in the JavaScript code.  See the "click: function()" part of that mess. -->

// this one's prolly redundant because it's in the main table, too.
	<input type="hidden" name="owner" id="editowner" value="" . $owner . "" />
	<input type="hidden" name="id" id="editid" value="" />

		<table class="popuptable">

		<thead>
		<tr>
			<th colspan="2">
				<h3 id="companyname"> </h3>
			</th>
		</tr>
		</thead>

		<tbody>
		<tr>
			<td class="right">
				<label for="symbol">Symbol:</label>
			</td>
			<td>
				<input type="text" name="symbol" id="editsymbol" value="">
			</td>
		</tr>

		<tr>
			<td class="right">
				<!--  for="..." Specifies which form element a label is bound to -->
				<label for="quant">Quantity:</label>
			</td>
			<td>
				<input type="text" name="quant" id="editquant" value="">
			</td>
		</tr>
		<tr>
			<td>
				<label for="price">Price for each: </label>
			</td>
			<td>
				<input type="text" name="price" id="editprice" value="">
			</td>
		</tr>

		<tr>
			<td class="right">
			<label for="fee">Fee: </label>
			</td>
			<td>
				<input type="text" name="fee" id="editfee" value="">
			</td>
		</tr>

		<tr>
			<td class="right">
				<label for="datepicker">Date Purchased: </label>
			</td>
			<td>
				<input type="text" name="datepicker" id="editdatepicker" value="">
			</td>
		</tr>

		<tr>
			<td class="right">
				<label for="account">Which account: </label>
			</td>
 			<td>
				<select name="account" id="editaccount">
				<option value="FidelityIndividual">Fidelity Individual</option>
				<option value="FidelitySEP">Fidelity SEP</option>
				<option value="FidelityRoth">Fidelity Roth</option>
				<option value="ScottradeIRA">Scottrade IRA</option>
				</select>
			</td>
		</tr>
		</tbody>

		</table>
	</form>
</div>


return $result;
    } // end  makeStocksTable($owner,$stocks)


    public function makeStocksTBODY($stocks){
$result = "
$agrigateChangeDollars = 0;
$agrigateCostDollars = 0;
$agrigateCurrentValue = 0;
$neg = "neg
$pos = "pos
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

    <tr>

    <td>
    <input type="checkbox" name="remove[]" value="" . (string)$s['_id'] . ""> // CHECKBOX
    </td>

    <td>
    	<input type="image" name="editfields" . (string)$s['_id'] . "" id="edit" . (string)$s['_id'] . "" // Edit button
    	src="images/gtk_edit.png"
    	alt="Edit this row"
    	value="" . (string)$s['_id'] . "">  //onclick="edit();"
    // opens a pop-up form as an overlay (id: #the_edit_form).
    // This is inline because each edit button needs its own unique id.
    	<script>
    //POZOR!!: if you add this comment to $result, it will COMMENT OUT THE WHOLE CONTENTS OF THE SCRIPT TAGS!!!
    // adding multiple $(documents).ready()s is not a problem. All will be executed on the ready event.
    		$(document).ready(function() {
    		$("#edit" . (string)$s['_id'] . "").click(function(e){
    //			alert("in the <script> function");
    //			alert($("input[name='editfields" . (string)$s['_id'] . "']").val());
    //			alert("" . $s['Name'] . "");
    //POZOR!!: if you add this comment to $result, it will COMMENT OUT THE WHOLE CONTENTS OF THE SCRIPT TAGS!!!
    // this group is to populate 'existing' fields into the form.
    			$("#companyname").text("" 	. $s['Name'] . "");
    			$("#editsymbol").val("" 		. $s['symbol'] . "");
    			$("#editquant").val("" 		. $s['purchasequantity'] . "");
    			$("#editprice").val("" 		. $s['purchaseprice'] . "");
    			$("#editfee").val("" 		. $s['purchasefee'] . "");
    			$("#editdatepicker").val("" 	. $s['purchasedate'] . "");
    			$("#editaccount").val("" 	. $s['account'] . "");
    			$("#editid").val("" 	. (string)$s['_id'] . "");
    			$("#editowner").text("" 	. $s['owner'] . "");
//						e.preventDefault();
    			$('#the_edit_form')
    				.dialog('open');
    		});
    		});
    	</script>
    </td>

    <td class="left">$s['Name'] . "</td>
    <td class="left">$s['symbol'] . "</td>
    <td class="right current">$$s['LastTradePriceOnly'] . "</td>
    <td class="
    $result .= ($s['Change']>0) ? $pos : $neg;
     current">$" . $s['Change'] . "</td>
    <td  class="right 
    $result .= ($percentchangetoday>0) ? $pos : $neg;
     current">number_format($percentchangetoday, 2, '.', ',') . "%</td>
    <td  class="right 
    $result .= ($totalChangeDollar>0) ? $pos : $neg;
    ">$number_format($totalChangeDollar, 2, '.', ',') . "</td>
    <td  class="right 
    $result .= ($totalChangePercent>0) ? $pos : $neg;
    ">number_format($totalChangePercent, 2, '.', ',') . "%</td>
    <td class="right">$number_format($totalCurrentValue, 2, '.', ',') . "</td>
    <td class="right">$number_format($totalCost, 2, '.', ',') . "</td>
    <td  class="right">$s['purchasequantity'] . "</td>
    <td class="right">$s['purchaseprice'] . "</td>
    <td class="right">number_format($s['purchasefee'], 2, '.', ',')  . "</td>
    <td class="left">$s['account'] . "</td>
    <td class="left">$s['purchasedate'] . "</td>


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
    <td>
    	<table class="graph" style='border:0  !important; padding: 0 !important; margin: 0 !important;'>
    		<tr class="graph" style='border:0  !important; padding: 0 !important; margin: 0 !important;'>
    			<td class="left graph" style='border:0  !important; padding: 0 !important; margin: 0 !important;' id="graph_" . (string)$s['_id'] . "">

    // ONLY SHOW 52 WEEK DATA, not longer ago than that (may have gained or lost more, but only show 1 year)
    // this boolean does 2 things: controls whether graph is red or green,
    // and controls order in graph of purchase price & current price
    $redboolean = (($s['LastTradePriceOnly'] - $s['purchaseprice']) < 0) ? TRUE : FALSE;
    $graphcolor = ($redboolean) ? "#ffb3b3" /*reddish*/ : "#d5ff80" /*greenish*/;

    // guard against stocks purchased more than 52 weeks ago at a lower or higher price
    $purchPriceTooLow = ($s['YearLow'] > $s['purchaseprice']) ? TRUE : FALSE;
    $purchPriceTooHigh = ($s['YearHigh'] < $s['purchaseprice']) ? TRUE : FALSE;
    //Then we can only have two colored ranges instead of three

    <script>

    // handle possible overflow of 52 week values
    if($purchPriceTooLow){
$pp = $s['YearLow'];
    }elseif($purchPriceTooHigh){
$pp = $s['YearHigh'];
    }else{
$pp = $s['purchaseprice'];
    }

    // this thing holds the 'ticks' -- the axis values
    var threshold_" . (string)$s['_id'] . " = d3.scale.threshold().domain([".$s['YearLow'];
    if($redboolean){// will be red, showing loss
,$s['LastTradePriceOnly'] . "," . $pp;
    }else{//willbe green showing gain
,$pp . "," . $s['LastTradePriceOnly'];
    }
    ," . $s['YearHigh']. "])
    //orig:  .range(["#ffffff","#ffffff", "#a0b28f", "#d8b8b3", "#b45554", "#760000","#ffffff"]);
    .range(["#ffffff","#ffffff", "" . $graphcolor . "", "#ffffff"]);

    // A position encoding for the key only. --that is, for the gain/loss part of the graph
    var x_" . (string)$s['_id'] . " = d3.scale.linear() // .domain([0, 1]) // original was percentages between 0 and 1
    .domain([".$s['YearLow'].",$s['YearHigh']."]).range([0, 200]); // range is the width of the graph within its container (see 'width' var)

    var xAxis_" . (string)$s['_id'] . " = d3.svg.axis().scale(x_" . (string)$s['_id'] . ").orient("bottom").tickSize(9).tickValues(threshold_" . (string)$s['_id'] . ".domain())
    .tickFormat(function (d) { return ''; }); // this should 'hide' x axis lables (very clever!)
    // .tickFormat(d3.format("$,.2f"));
    // 'width' and 'height' are size of 'container' of the graph
    var svg_" . (string)$s['_id'] . " = d3.select("#graph_" . (string)$s['_id'] . "").append("svg").attr("width", 202).attr("height", 10); // height of 10 prevents D3's xAxis area from being shown (view area too samall to show it).  XAxis is handled 'manual' by means of a table with data below here.

    var g_" . (string)$s['_id'] . " = svg_" . (string)$s['_id'] . ".append("g").attr("class", "key").attr("transform", "translate(0, 1)"); // 0px from left, 0 px from the bottom

    g_" . (string)$s['_id'] . ".selectAll("rect").data(threshold_" . (string)$s['_id'] . ".range().map(function(color) {
    		var d_" . (string)$s['_id'] . " = threshold_" . (string)$s['_id'] . ".invertExtent(color);
    		if (d_" . (string)$s['_id'] . "[0] == null) d_" . (string)$s['_id'] . "[0] = x_" . (string)$s['_id'] . ".domain()[0];
    		if (d_" . (string)$s['_id'] . "[1] == null) d_" . (string)$s['_id'] . "[1] = x_" . (string)$s['_id'] . ".domain()[1];
    		return d_" . (string)$s['_id'] . 
    	}))
    .enter().append("rect")
    .attr("height", 7) // this is the height of the graph's horizontal bar
    .attr("x", function(d) { return x_" . (string)$s['_id'] . "(d[0]); })
    .attr("width", function(d) { return x_" . (string)$s['_id'] . "(d[1]) - x_" . (string)$s['_id'] . "(d[0]); })
    //.style("stroke", "#000") // makes just the middle block outlined with a line
    .style("fill", function(d) { return threshold_" . (string)$s['_id'] . "(d[0]); });

    g_" . (string)$s['_id'] . ".call(xAxis_" . (string)$s['_id'] . ");  // orig: .append("text")
    // orig: .attr("class", "caption")
    // orig: .attr("y", 0)
    // orig: .attr("y", -6)
    // orig: .text("Percentage of stops that involved force")
    // orig: ;
    //top line
    var borderPathTop_" . (string)$s['_id'] . " = svg_" . (string)$s['_id'] . ".append("rect")
    			.attr("x", 0) // 0px from the absolute left
    			.attr("y", 8) // 0px from the absolute top of where the graph is drawn
    			.attr("height", 1)
    			.attr("width", 201);
    //bottom line
    var borderPathBottom_" . (string)$s['_id'] . " = svg_" . (string)$s['_id'] . ".append("rect")
    			.attr("x", 0)
    			.attr("y", 1)
    			.attr("height", 1)
    			.attr("width", 201);

    </script>
    /*****************************************************************************************
     * End of D3.js graph in this table cell
     */

    			</td>
    		</tr>
    		<tr class="graph" style='border:0  !important; height: 8px !important; padding: 0 !important; margin: 0 !important;'>
    			<td class="graph center" style='border:0  !important; padding: 0 !important; margin: 0 !important;'>
    				<table class="graph" style='border:0  !important; padding: 0 !important; margin: 0 !important;'>
    					<tr class="graph" style='border:0  !important; height: 8px !important; padding: 0 !important; margin: 0 !important;'>

    $low = ($s['YearLow']=='') ? 'no data' : '$'.$s['YearLow'];
    $high = ($s['YearHigh']=='') ? 'no data' : '$'.$s['YearHigh'];



    						<td class="left graph" style='border:0 !important; padding: 0 !important; margin: 0 !important; width: 25%;'>$low</td>
    						<td class="center graph" style='border:0  !important; padding: 0 !important; margin: 0 !important; width: 50%;'>Last: $$s['LastTradePriceOnly']</td>
    						<td class="right graph" style='border:0  !important; padding: 0 !important; margin: 0 !important; width: 25%;'>$high</td>
    					</tr>
    				</table>
    			</td>
    		</tr>
    	</table>
    	</td>
    </tr>




    </tr>

}// end of foreach loop, populating each row

/* build LAST ROW OF ENTIRE TABLE, which shows agrigate values */
<tr>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td class="right">$" . number_format($agrigateChangeDollars, 2, '.', ',') . "</td>
<td></td>
<td class="right">$" . number_format($agrigateCurrentValue, 2, '.', ',') . "</td>
<td class="right">$" . number_format($agrigateCostDollars, 2, '.', ',') . "</td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
</tr>




//<script src="js/tablebuttons.js"></script>


return $result;
    } // end makeStocksTBODY($stocks)

}

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