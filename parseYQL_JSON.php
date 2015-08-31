<!doctype html> 
<html>
	<head>
		<title> Testing Rickshaw / D3 tools to make graphs </title>
		<meta charset="utf-8">

		<!-- dumb mistake: putting "/" at beginning of source files doesn't mean 'this directory' -->
		<link rel="stylesheet" href="rickshaw.min.css">
		<script src="d3.min.js"></script> 
		<script src="d3.v2.js"></script>
		<script src="d3.layout.min.js"></script> 
		<script src="rickshaw.min.js"></script> 

		<style>
			#chart_container {
					position: relative;
					font-family: Arial, Helvetica, sans-serif;
			}
			#chart {
					position: relative;
					left: 40px;
			}
			#y_axis {
					position: absolute;
					top: 0;
					bottom: 0;
					width: 40px;
			}
		</style>
		
	</head>
 
	<body>
		
		<div id="legend"></div>
		<div id="chart_container">
			<p> </p>
				<div id="y_axis"></div>
				<div id="chart"></div>
		</div>

<?php		
	$json_string = file_get_contents("yql");
	$recordArr = json_decode($json_string);
	$data1 = "{ ";
	$notFirst = 0;
	foreach($recordArr->query->results->quote as $Q){
		if(0 < $notFirst++){ $data1 .= ", { "; }
		
		$dt = strtotime($Q->Date);//good news! rickshaw uses seconds, not milliseconds!
		//echo $dt . "<br>";
		//$data1 .= "x: $dt, y: $Q->Close }";
		$data1 .= "x: $Q->Date, y: $Q->Close }";
	}
	echo $data1;	
	echo "<br>";
	
	$json2 = file_get_contents("yql_fb");
	$recordArr2 = json_decode($json2);
	$data2 = "{ ";
	$notFirst2 = 0;
	foreach($recordArr2->query->results->quote as $Q){
		if(0 < $notFirst2++){ $data2 .= ", { "; }
				
		$dt = strtotime($Q->Date);//good news! rickshaw uses seconds, not milliseconds!
		echo $Q->Date . " becomes " . $dt . " and back again " . date("Y-m-d H:i:s", $dt) . "<br>";				
		//$data2 .= "x: $dt, y: $Q->Close }";
		$data2 .= "x: $Q->Date, y: $Q->Close }";
	}	
	echo $data2;
	echo "<br>";	
	//$data2 .= "{ x: 23, y: 40 }, { x: 24, y: 49 }, { x: 25, y: 38 }, { x: 26, y: 30 }, { x: 27, y: 32 }";
?>
		<script>
			var graph = new Rickshaw.Graph( {
				element: document.querySelector("#chart"),
				width: 150,
				height: 600,
				renderer: 'area',
				stroke: true,
			
				series: [ {
					data: [ <?php echo $data1; ?> ],
					color: '#9cc1e0',
                    name: 'Massatoosuss'
				}, {
					data: [ <?php echo $data2; ?> ],
					color: '#0274D3',
                    name: 'New York'
				}  ]
			} );
			
			var xAxis = new Rickshaw.Graph.Axis.Time({
				graph: graph,
				timeFixture: new Rickshaw.Fixtures.Time(),
				//timeUnit: days
			});
			
			//var xAxis = d3.svg.axis().scale(x).orient("bottom").tickFormat(d3.time.format("%m-%d"));
			
			var yAxis = new Rickshaw.Graph.Axis.Y( {
				graph: graph,
				orientation: 'left',
				tickFormat: Rickshaw.Fixtures.Number.formatKMBT,
				element: document.getElementById('y_axis'),
			} );
			
			var legend = new Rickshaw.Graph.Legend({
				graph: graph,
				element: document.querySelector('#legend')
			});
			graph.render();
			
			//var axes = new Rickshaw.Graph.Axis.Time({
			//	graph: graph
			//});
			//axes.render();


		</script>
	</body>
</html>