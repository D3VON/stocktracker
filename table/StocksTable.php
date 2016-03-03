<?php

/**
 * Created by PhpStorm.
 * User: devonmcb
 * Date: 2/13/16
 * Time: 4:26 PM
 *
 * Using this as a place within the IDE to fix another php class before clobbering the old one with 'improved' stuff.
 * --I'm copying it onto the 'live' StocksTable.php and there are still some bad errors....
 */
class StocksTable
{
    // This table is holds all the stock purchases and buttons

    public function makeStocksTable($owner,$stocks){

        if(!is_array($stocks)){
            return "Apologies to the user: Unable to obtain current data.  Please try again later.";
        }


        $starttable_and_head = <<<TABLEHEAD
        
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
                    <th colspan="4">Activity Today</th>
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
                    <th class="sortByColumn">Today's<br> Gain/Loss</th>
                    <th class="sortByColumn">Total<br> $ Change</th>
                    <th class="sortByColumn">Total<br> % Change</th>
                    <th class="sortByColumn">Total<br> Value</th>
                    <th class="sortByColumn">Total<br> Cost</th>
                    <th>Quantity</th>
                    <th>Purch. Price</th>
                    <th>Fee</th>
                    <th class="sortByColumn">Account</th>
                    <th class="sortByColumn">Purchase<br> Date</th>
                    <th class="italic">& your gain/loss in 52 weeks</th>
                </tr>
            </thead>
            <tbody>
                {$this->makeStocksTBODY($stocks)}
            </tbody>
        </table>
TABLEHEAD;

        // this div holds the little "Add a stock purchase" pop-up form (as a table)
        //<!-- I got it here: https://github.com/apanzerj/Former-For-Zendesk/blob/Lesson-2-Branch/former.html#L14
        //and here: http://jsfiddle.net/eARdt/170/ and someplace at zendesk (google it) -->

        $popups = <<<POPUPS
            <div id="the_add_form"  title="Add a purchase">
                <!-- Pop-up form hidden by JavaScript until button is clicked -->
                <form id="addsymbol" action="addPurch.php" method="POST" name="addstock" enctype="multipart/form-data">

                    <!-- Note To Self: variables are passed via POST, but also have to be 'hand loaded' into the strings -->
                    <!-- that 'look like' a GET string, down in the JavaScript code.  See the "click: function()" part of that mess. -->
                    <!-- This input is prolly redundant because it's in the main table, too, but I don't have the time or energy to test it -->
                    <input type="hidden" name="owner" id="owner" value="$owner" />

                    <table class="popuptable">
                        <tr>
                            <td class="right">
                                <label for="symbol">Symbol:</label>
                            </td>
                            <td>
                                <input type="text" name="symbol" id="symbol">
                            </td>
                        </tr>
                        <tr>
                            <td class="right">
                                <!--  for="..." Specifies which form element a label is bound to -->
                                <label for="quant">Quantity:</label>
                            </td>
                            <td>
                                <input type="text" name="quant" id="quant">
                            </td>
                        </tr>
                        <tr>
                            <td class="right">
                                <label for="price">Price for each: </label>
                            </td>
                            <td>
                                <input type="text" name="price" id="price">
                            </td>
                        </tr>
                        <tr>
                            <td class="right">
                                <label for="fee">Fee: </label>
                            </td>
                            <td>
                                <input type="text" name="fee" id="fee">
                            </td>
                        </tr>
                        <tr>
                            <td class="right">
                                <label for="datepicker">Date Purchased: </label>
                            </td>
                            <td>
                                <input type="text" name="datepicker" id="datepicker">
                            </td>
                        </tr>
                        <tr>
                            <td class="right">
                                <label for="account">Which account: </label>
                            </td>
                            <td>
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


            <!-- These two tables are very similar, so it's a good example of how the id's must be different. -->
            <!-- This div holds the "Edit a stock purchase" pop-up form (as a table) -->
            <!-- Pop-up form hidden by JavaScript until button is clicked -->
            <!-- I got it here: https://github.com/apanzerj/Former-For-Zendesk/blob/Lesson-2-Branch/former.html#L14 -->
            <!-- and here: http://jsfiddle.net/eARdt/170/ and someplace at zendesk (google it) -->

            <div id="the_edit_form"  title="Edit a purchase">

	            <form id="editsymbol" action="editPurch.php" method="POST" name="editstock" enctype="multipart/form-data">
                    <!-- Note To Self: variables are passed via POST, but also have to be 'hand loaded' into the strings -->
                    <!-- that 'look like' a GET string, down in the JavaScript code.  See the "click: function()" part of that mess. -->
                    <!-- This input is prolly redundant because it's in the main table, too, but I don't have the time or energy to test it -->
                    <input type="hidden" name="owner" id="editowner" value="" . $owner . "" />
                    <input type="hidden" name="id" id="editid" value="" />

                    <table class="popuptable">
                        <thead>
                            <tr>
                                <th colspan="2">
                                    <h2 id="companyname"> </h3>
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
            </div
POPUPS;
        return $starttable_and_head . $popups;

    } // end  makeStocksTable()


    /**
     * @param $stocks
     *
     * called by makeStocksTable()
     *
     * @return String $tablebody a long, long string containing rows of data, each row containing a D3 chart
     */
    public function makeStocksTBODY($stocks){

        // prepare values derived from user data and current stock quote(s)
        $aggregateChangeDollars = 0;
        $aggregateCostDollars = 0;
        $aggregateCurrentValue = 0;
        $todaysTotalGainLoss = 0;
        $neg = "neg";// just sets css color to red
        $pos = "pos";// just sets css color to green

        /* Doing this based on: http://stackoverflow.com/questions/104516/calling-php-functions-within-heredoc-strings
           ...which wasn't the top answer
           ... like this:  {$fn(time())}
         */
        $number_format_hack = 'number_format';

        $tablebody = ""; // will receive many heredocs created by the foreach loop below

        foreach ($stocks as $s){
            $totalCost = $s['purchasequantity'] * $s['purchaseprice'] + $s['purchasefee'];
            $aggregateCostDollars += $totalCost;
            $totalCurrentValue =  $s['purchasequantity'] * $s['LastTradePriceOnly'] - $s['purchasefee'];
            $aggregateCurrentValue += $totalCurrentValue;
            //$dollarchange = $s['LastTradePriceOnly'] - $s['purchaseprice'];
            $totalChangeDollar = $totalCurrentValue - $totalCost;
            $aggregateChangeDollars += $totalChangeDollar;

            $totalChangePercent = $totalCost != 0 ? $totalChangeDollar / $totalCost * 100 : 0;
            $percentchangetoday = $s['LastTradePriceOnly'] != 0 ? $s['Change'] / $s['LastTradePriceOnly'] * 100 : 0;
            $id = (string)$s['_id'];
            $pozornegDollarChng = ($s['Change']>0) ? $pos : $neg;
            $pozornegPercentChng = ($percentchangetoday>0) ? $pos : $neg;
            $pozornegTotDollarChng = ($totalChangeDollar>0) ? $pos : $neg;
            $pozornegTotPercentChng = ($totalChangePercent>0) ? $pos : $neg;
            $todaysGainLoss = $s['Change'] * $s['purchasequantity'];
            $todaysTotalGainLoss += $todaysGainLoss;

            $tablebody .= <<<BEGINTABLEBODY
    <tr>
        <td>
            <input type="checkbox" name="remove[]" value="$id">
        </td>

        <td>
            <input type="image" name="editfields$id" id="edit$id" src="images/gtk_edit.png" alt="Edit this row" value="$id">
                <!-- opens a pop-up form as an overlay (id: #the_edit_form). -->
                <!-- This is inline because each edit button needs its own unique id. -->
                <!-- adding multiple $(documents).ready()s is not a problem. All will be executed on the ready event. -->
                            <!-- for testing: -->
                            <!-- alert("in the <script> function"); -->
                            <!-- alert($("input[name='editfields$id']").val()); -->
                            <!-- alert("{$s['Name']}"); -->
                            <!-- this group is to populate 'existing' fields into the form. -->
            <script>
                $(document).ready(function() {
                    $("#edit$id").click(function(e){
                        $("#companyname").text("{$s['Name']}");
                        $("#editsymbol").val("{$s['symbol']}");
                        $("#editquant").val("{$s['purchasequantity']}");
                        $("#editprice").val("{$s['purchaseprice']}");
                        $("#editfee").val("{$s['purchasefee']}");
                        $("#editdatepicker").val("{$s['purchasedate']}");
                        $("#editaccount").val("{$s['account']}");
                        $("#editid").val("$id");
                        $("#editowner").val("{$s['owner']}");
                        <!-- e.preventDefault(); -->
                        $('#the_edit_form')
                            .dialog('open');
                    });
                });
            </script>
        </td>

        <td class="left">{$s['Name']}</td>
        <td class="left">{$s['symbol']}</td>
        <td class="right current">\${$s['LastTradePriceOnly']}</td>
        <td class="$pozornegDollarChng current">\${$s['Change']}</td>
        <td class="right $pozornegPercentChng current">{$number_format_hack($percentchangetoday, 2, '.', ',')}%</td>
        <td class="right $pozornegDollarChng current">\${$number_format_hack($todaysGainLoss, 2, '.', ',')}</td>


        <td class="right $pozornegTotDollarChng">\${$number_format_hack($totalChangeDollar, 2, '.', ',')}</td>
        <td class="right $pozornegTotPercentChng">{$number_format_hack($totalChangePercent, 2, '.', ',')}%</td>
        <td class="right">\${$number_format_hack($totalCurrentValue, 2, '.', ',')}</td>
        <td class="right">\${$number_format_hack($totalCost, 2, '.', ',')}</td>
        <td class="right">{$s['purchasequantity']}</td>
        <td class="right">\${$number_format_hack($s['purchaseprice'], 2, '.', ',')}</td>
        <td class="right">\${$number_format_hack($s['purchasefee'], 2, '.', ',')}</td>
        <td class="left">{$s['account']}</td>
        <td class="left">{$s['purchasedate']}</td>

        <td>
            <table class="graph" style='border:0  !important; padding: 0 !important; margin: 0 !important;'>
                <tr class="graph" style='border:0  !important; padding: 0 !important; margin: 0 !important;'>
                    <td class="left graph" style='border:0  !important; padding: 0 !important; margin: 0 !important;' id="graph_$id">
BEGINTABLEBODY;

                /***************************************************************************************
                 ***************************************************************************************
                 * This block is INLINE D3.js.  It is originally a graph I found called "threshold key"
                 * but I've repurposed it to show 52 week high, low, and the stock's gain or loss
                 *
                 * See my /examples/thresholdkeygraphexample.html for detail on how to build this thing
                 *
                 * NOTE: very shaggy hacking: Instead of declaring, e.g., "var x" many times
                 * (this code is called many times in a loop) causing it to be declared many times
                 * in the same script, I'm 'augmenting' the name, e.g., "var x", with the database id
                 * of each stock thusly: _$id so as to make each declaration UNIQUE!
                 ***************************************************************************************
                 ***************************************************************************************/

                // ONLY SHOW 52 WEEK DATA, not longer ago than that (may have gained or lost more, but only show 1 year)
                // this boolean does 2 things: controls whether graph is red or green,
                // and controls order in graph of purchase price & current price
                $redboolean = (($s['LastTradePriceOnly'] - $s['purchaseprice']) < 0) ? TRUE : FALSE;
                $graphcolor = ($redboolean) ? "#ffb3b3" /*reddish*/ : "#d5ff80" /*greenish*/;

                // guard against stocks purchased more than 52 weeks ago at a lower or higher price
                $purchPriceTooLow = ($s['YearLow'] > $s['purchaseprice']) ? TRUE : FALSE;
                $purchPriceTooHigh = ($s['YearHigh'] < $s['purchaseprice']) ? TRUE : FALSE;
                //Then we can only have two colored ranges instead of three

                // handle possible overflow of 52 week values
                if($purchPriceTooLow){
                    $pp = $s['YearLow'];
                }elseif($purchPriceTooHigh){
                    $pp = $s['YearHigh'];
                }else{
                    $pp = $s['purchaseprice'];
                }

                if($redboolean){ //  will be red, showing loss
                    $middleBar = $s['LastTradePriceOnly'] . "," . $pp;
                }else{ // willbe green showing gain
                    $middleBar = $pp . "," . $s['LastTradePriceOnly'];
                }

                $low = ($s['YearLow']=='') ? 'no data' : '$'.$s['YearLow'];
                $high = ($s['YearHigh']=='') ? 'no data' : '$'.$s['YearHigh'];
                $d_withID = "d_" . $id; // this is a real, ugly, hack as a result of heredoc.  See inside <script>
                // end local php block for use in the following heredoc block of javascript

            $tablebody .= <<<GRAPH

                        <!--  DO NOT PUT HTML COMMENTS INSIDE THE SCRIPT TAG. --Makes javascript go haywire. -->
                        <!--  I have a lot of comments in my "StocksTable_orig-worksVeryWell.php", and also... -->
                        <!--  ...in thresholdkeygraphexample.html -->
                        <script>

                            var threshold_$id = d3.scale
                                .threshold().domain([{$s['YearLow']},$middleBar,{$s['YearHigh']}])
                                .range(["#ffffff","#ffffff","$graphcolor","#ffffff"]);
                            var x_$id = d3.scale.linear()
                                .domain([{$s['YearLow']},{$s['YearHigh']}])
                                .range([0, 200]);
                            var xAxis_$id = d3.svg.axis()
                                .scale(x_$id).orient("bottom")
                                .tickSize(9)
                                .tickValues(threshold_$id.domain())
                                .tickFormat(function (d) { return ''; });
                            var svg_$id = d3.select("#graph_$id")
                                .append("svg")
                                .attr("width", 202)
                                .attr("height", 10);
                            var g_$id = svg_$id.append("g")
                                .attr("class", "key")
                                .attr("transform", "translate(0, 1)");
                            g_$id.selectAll("rect").data(threshold_$id.range().map(function(color) {
                                var $d_withID = threshold_$id.invertExtent(color);
                                if ({$d_withID}[0] == null) {$d_withID}[0] = x_$id.domain()[0];
                                if ({$d_withID}[1] == null) {$d_withID}[1] = x_$id.domain()[1];
                                return d_$id;
                            }))
                            .enter().append("rect")
                            .attr("height", 7)
                            .attr("x", function(d) { return x_$id(d[0]); })
                                .attr("width", function(d) { return x_$id(d[1]) - x_$id(d[0]); })
                                .style("fill", function(d) { return threshold_$id(d[0]); });
                            g_$id.call(xAxis_$id);

                            var borderPathTop_$id = svg_$id.append("rect")
                                    .attr("x", 0)
                                    .attr("y", 8)
                                    .attr("height", 1)
                                    .attr("width", 201);
                            var borderPathBottom_$id = svg_$id.append("rect")
                                    .attr("x", 0)
                                    .attr("y", 1)
                                    .attr("height", 1)
                                    .attr("width", 201);
                        </script>
                                <!-- End of inline D3.js graph in this table cell -->
                    </td>
                </tr>
                <tr class="graph" style='border:0  !important; height: 8px !important; padding: 0 !important; margin: 0 !important;'>
                    <td class="graph center" style='border:0  !important; padding: 0 !important; margin: 0 !important;'>
                        <!-- HERE, WE MAKE A TABLE HOLDING X-AXIS VALUES -- instead of doing it in D3, so we have better control -->
                        <table class="graph" style='border:0  !important; padding: 0 !important; margin: 0 !important;'>
                            <tr class="graph" style='border:0  !important; height: 8px !important; padding: 0 !important; margin: 0 !important;'>
                                <td class="left graph" style='border:0 !important; padding: 0 !important; margin: 0 !important; width: 25%;'>$low</td>
                                <td class="center graph" style='border:0  !important; padding: 0 !important; margin: 0 !important; width: 50%;'>Last: \${$s['LastTradePriceOnly']}</td>
                                <td class="right graph" style='border:0  !important; padding: 0 !important; margin: 0 !important; width: 25%;'>$high</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</tr>
GRAPH;

        }// end of foreach loop, populating each row


        $tablebody .= <<<FINISHTABLEBODY
            <!-- build LAST ROW OF ENTIRE TABLE, which shows aggregate values -->
            <tr bgcolor="#E6E6E6">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="right $pozornegDollarChng current">\${$number_format_hack($todaysTotalGainLoss, 2, '.', ',')}</td>
                <td class="right">\${$number_format_hack($aggregateChangeDollars, 2, '.', ',')}</td>
                <td></td>
                <td class="right">\${$number_format_hack($aggregateCurrentValue, 2, '.', ',')}</td>
                <td class="right">\${$number_format_hack($aggregateCostDollars, 2, '.', ',')}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <!-- If you're reading the PHP code for this: closing tbody and table tags are up in the function makeStocksTable() -->
            <!-- <script src="js/tablebuttons.js"></script> -->
FINISHTABLEBODY;

        return $tablebody;
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