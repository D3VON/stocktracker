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

		<!-- h2>June 22, 2014</h2 -->
		<p><img src="images/bullbear.gif" alt="bull and bear"></p>
		<p>
		  Here's a table presenting some stocks: 
		</p>
		
		<!-- Pop-up form hidden by JavaScript until button is clicked -->
		<!-- I got it here: https://github.com/apanzerj/Former-For-Zendesk/blob/Lesson-2-Branch/former.html#L14
		and here: http://jsfiddle.net/eARdt/170/ and someplace at zendesk (google it) -->
		<form id="addsymbol" method="post" action="addstock.php" name="addstock">	
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
	<input  type="button" id="clicky" value="Add a symbol">



</body>
</html>