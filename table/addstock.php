<!doctype html> 
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Add a Stock</title>

		<!-- Bootstrap -->
		<!-- link href="css/bootstrap.min.css" rel="stylesheet" -->

		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		  <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
		<!-- dumb mistake: putting "/" at beginning of source files doesn't mean 'this directory' -->
		<link type="text/css" rel="stylesheet" href="stocks.css">
		
	</head>
	<body>

		<h1>Add a stock to track</h1>
		<form action="PROCESS THE ADD!" method="POST">
			<table>
				<tr>
					<td>Symbol:</td>
					<td><input type="text" name="symbol" value="" /> </td>
				</tr>
				<tr>
					<td>Quantity:</td> 
					<td><input type="text" name="quant" value="" /> </td>
				</tr>
				<tr>
					<td>Price:</td> 
					<td><input type="text" name="price" value="" /> </td>
				</tr>
				<tr>
					<td>Date:</td> 
					<td><input type="text" name="date" value="" /> </td>
				</tr>
				<tr>
					<td></td>
					<td><input type="submit" value="Add Stock"/></td>
				</tr>
			</table>
		</form>
				
	</body>
</html>