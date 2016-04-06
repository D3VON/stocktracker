<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> Graphs using Rickshaw / D3 </title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <!--script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script-->
    <!--script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script-->
    <![endif]-->
    <!-- dumb mistake: putting "/" at beginning of source files doesn't mean 'this directory' -->
    <link rel="stylesheet" href="rickshaw.min.css">
    <script src="d3.min.js"></script>
    <script src="d3.v2.js"></script>
    <script src="d3.layout.min.js"></script>
    <script src="rickshaw.min.js"></script>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

    <?php
    include_once('MongoToYQL_Adapter.php');

    $owner = htmlspecialchars($_GET["owner"]);
//    $owner = "me";

    $db = new MongoToYQL_Adapter;
    $quotes = $db->getAllStocksByOwner($owner);
    /* PROBLEM: $db->getAllStocksByOwner($owner) could have many purchases of the same stock,
     * so, we have to get rid of the redundancies. My solution is to map symbols as we
     * loop through what was returned, then skip if it's already in the map.
     */
    $mapA = array();
    //set up unique styles for each graph in header
    foreach($quotes as $q){
        if(!array_key_exists($q['symbol'],$mapA)){
            $mapA[$q['symbol']] = NULL;
            $headStyle = "<style>"
                ."			#".$q['symbol']."_chart_container {"
                ."					width: 960px;"
                ."					position: relative;"
                ."					font-family: Arial, Helvetica, sans-serif;"
                ."			}"
                ."			#".$q['symbol']."_chart {"
                ."					position: relative;"
                ."					left: 40px;"
                ."			}"
                ."			#".$q['symbol']."_y_axis {"
                ."					position: absolute;"
                ."					top: 0;"
                ."					bottom: 0;"
                ."					width: 40px;"
                ."			}"
                ."			#".$q['symbol']."_legend {"
                ."				text-align: center;"
                ."			}"
                ."		</style>";
            echo $headStyle;
        }
    }
    echo "</head><body><h1>Stock Charts</h1>";
    $mapB = array();
    $graphInfo = array();
    foreach($quotes as $q){
        // guard against redundancies.  See 'PROBLEM' note above
        if(!array_key_exists($q['symbol'],$mapB)) {
            $mapB[$q['symbol']] = NULL;

            // hard-code period now, but that should be selected by user in the future.
            //$result = getD3Coordinates($q['symbol'], $numPeriods, $typePeriods, date('Y-m-d'));
            // contains 2d array, each array has 3 elements: 'symbol','coords','min','max'
            $graphInfo[] = $db->getD3Coordinates($q['symbol'], 3, 'months', date('Y-m-d'));

            // build the start date from arguments given (calculated back from $endDate)
            $numPeriods = 3;
            $typePeriods = 'months';
            $endDate = date('Y-m-d');
            //$startDate = strtotime(date('Y-m-d', strtotime("-$numPeriods $typePeriods", strtotime($endDate))));

//            if("goog" == $q['symbol'] || "bac" == $q['symbol']){
//
//                echo "<pre>"; var_dump($graphInfo); echo "</pre>";
//
//// failed attempt at I forget what
////                foreach($graphinfojson["coords"] as $coord){
////                    //if($coord["x"]>$startDate){
////                        echo $coord["x"] . ", " . $coord["y"];
////                    //}
////                }
//            }


            // set up div to hold each graph
            $stockDiv = ""
                . "	<table>"
                . "		<tr>"
                . "			<td>" . $q['Name'] . "</td>"
                . "		</tr>"
                . "	</table>"
                . "<div id=\"" . $q['symbol'] . "_chart_container\">"
                . "	<table>"
                . "		<tr>"
                . "			<td>"
                . "				<div id=\"" . $q['symbol'] . "_legend\"></div>"
                . "			</td>"
                . "			<td>"
                . "			</td>"
                . "			<td>"
                . "				<div id=\"" . $q['symbol'] . "_y_axis\"></div>"
                . "				<div id=\"" . $q['symbol'] . "_chart\"></div>"
                . "			</td>"
                . "		</tr>"
                . "	</table>"
                . "</div>";
            echo "<br>" . $stockDiv . "<br><hr>";
        }
    }

    //	use Stock objects to build rickshaw graph
    //while($q=pg_fetch_assoc($result)){
    foreach($graphInfo as $coordInfo)
    {
        $text = ""
            ."<script>"
            ."	var graph = new Rickshaw.Graph( {"
            ."		element: document.getElementById(\"".$coordInfo['symbol']."_chart\"),"
            ."		width: 400,"
            ."		height: 100,"
            ."		renderer: 'line',"
            ."		stroke: true,"
            //."		'stroke-width': 1," // won't work
            ."		series: [ ";
        $color = "#6699FF"; //array_pop($colors);
        $text .= "{	data: [  ".$coordInfo['coords']." ], "
            ."		scale: d3.scale.linear().domain(["
            .		$coordInfo['min'].", ".$coordInfo['max']."]).nice(),"
            /* NOTE: coord time period limited manually in MongoToYQL_Adapter.php in getHistory($symbol) until I can make a JS thing that the user can manipulate the date range with. */
            ."		color: '$color', name: '".$coordInfo['symbol']."'}";
        $text .= "] } );"
            ."	var xAxis = new Rickshaw.Graph.Axis.Time({"
            ."		graph: graph,"
            ."		timeFixture: new Rickshaw.Fixtures.Time(),"
            ."	});"
            ."	var yAxis = new Rickshaw.Graph.Axis.Y.Scaled( {"
            ."		graph: graph,"
            ."		orientation: 'left',"
            ."		tickFormat: Rickshaw.Fixtures.Number.formatKMBT,"
            ."		scale: d3.scale.linear().domain(["
            .		$coordInfo['min'].", ".$coordInfo['max']."]).nice(),"
            ."		element: document.getElementById('".$coordInfo['symbol']."_y_axis'),"
            ."	} );"
            ."	var legend = new Rickshaw.Graph.Legend({"
            ."		graph: graph,"
            ."		element: document.getElementById('".$coordInfo['symbol']."_legend')"
            ."	});"
            ."	new Rickshaw.Graph.HoverDetail({"
            ."	  graph: graph"
            ."	});"
            ."	graph.render();"
            ."		</script>";
        echo $text;
    }

    //$colors = array("#0000FF", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33", "#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33","#0066FF", "#0099FF", "#00FFFF", "#66FF99", "#6699FF", "#339900", "#33FF33");
?>