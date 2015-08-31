		
$(document).ready(function() {
	
	// opens a pop-up form as an overlay (id: #the_add_form) that has its own buttons
	$('#addy').click(function(e){	
		//e.preventDefault();
	    $('#the_add_form').dialog('open');
	});

	$( "#datepicker,#editdatepicker" ).datepicker(
			{altFormat: "yy-mm-dd"}
	);
	
	$('#the_edit_form').dialog({
	    autoOpen: false,
	    height: 380,
	    width: 400,
	    modal: true,
	    buttons: [
	    	{
		    	text: "Cancel",
		        click: function() {
		            $(this).dialog("close");
	        	}
	        },
		    {
	            text: "Submit",
		        click: function() {
		        	
		       		var symbol = $("#editsymbol").val();
		       		var quant = $("#editquant").val();
		       		var price = $("#editprice").val();
		       		var datepicker = $("#editdatepicker").val();
		       		var fee = $("#editfee").val();
		       		var account = $("#editaccount").val();
		       		var owner = $("#editowner").val();
		       		var id = $("#editid").val();
		       		// Returns successful data submission message when the entered information is stored in database.
		       		var dataString = 'symbol='+ symbol + '&quant='+ quant + '&price='+ price + '&datepicker='+ datepicker + '&fee='+ fee + '&account='+ account + '&owner='+ owner + '&id=' + id;
		       		if(symbol==''||quant==''||price==''||datepicker==''||fee==''||account==''||owner=='')
		       		{
		       			alert("Please Fill All Fields");
		       		}
		       		else
		       		{
		       			$.ajax({
		       				type: "POST",
		       				url: "editPurch.php",
		       				data: dataString,
		       				cache: false,
		       				success: function(result){
		       					//document.getElementById("stocktablebody").innerHTML = result;
		       					$("#stockstable tbody").html(result);
		       				}
		       			});
		       			//close this little input form overlay
			       		$( this ).dialog( "close" );
		       		}
		       		return false;
	           	}
	       	}
	    ]
	});
	
	$('#the_add_form').dialog({
	    autoOpen: false,
	    height: 340,
	    width: 400,
	    modal: true,
	    buttons: [
	    	{
		    	text: "Cancel",
		        click: function() {
		            $(this).dialog("close");
	        	}
	        },
		    {
	            text: "Submit",
		        click: function() {
		       		var symbol = $("#symbol").val();
		       		var quant = $("#quant").val();
		       		var price = $("#price").val();
		       		var datepicker = $("#datepicker").val();
		       		var fee = $("#fee").val();
		       		var account = $("#account").val();
		       		var owner = $("#owner").val();
		       		// Returns successful data submission message when the entered information is stored in database.
		       		var dataString = 'symbol='+ symbol + '&quant='+ quant + '&price='+ price + '&datepicker='+ datepicker + '&fee='+ fee + '&account='+ account + '&owner='+ owner;
		       		if(symbol==''||quant==''||price==''||datepicker==''||fee==''||account==''||owner=='')
		       		{
		       			alert("Please Fill All Fields<br>" + dataString + "<br>");
		       		}
		       		else
		       		{
		       			$.ajax({
		       				type: "POST",
		       				url: "addPurch.php",
		       				data: dataString,
		       				cache: false,
		       				success: function(result){
		       					//document.getElementById("stocktablebody").innerHTML = result;
		       					$("#stockstable tbody").html(result);
		       				}
		       			});
		       			//close this little input form overlay
			       		$( this ).dialog( "close" );
		       		}
		       		return false;
	           	}
	       	}
	    ]
	});
	
	$('#selectAll').click(function(event){	
	    $(this).closest('table').find('td input:checkbox').prop('checked', this.checked);
	});

	$('#removie').click(function(event){ 
		//alert("we're in the jQuery #removie function.  Here's the remove array: " + $("input[name='remove[]']").val());	
		event.preventDefault();
		var remove = [];
		//var jsonString = JSON.stringify(dataArray);
		
		//stackoverflow.com/questions/24335862/how-i-get-array-checkbox-value-in-jquery
		$(':checkbox:checked').each(function(i){
			remove[i] = $(this).val();
		});
		//$("input[name='remove[]']").val();
		//alert("we're in the jQuery #removie function.  Here's the remove array: " + remove);	
		
		
		var owner = $("#owner").val();
		var dataString = 'remove='+ remove + '&owner='+ owner;				
		//var url = "removePurch.php"; //$(this).attr('action');// this attribute's action (specified in <form> tag)

		//alert("we're in the jQuery #removie function.  Here's the dataString: " + dataString);
		$.ajax({
			type: "POST",
			url: "removePurch.php",
			data: dataString,
			cache: false,
			success: function(result){
				//document.getElementById("stocktablebody").innerHTML = result;
				$("#stockstable tbody").html(result);
			}
		});
		return false;
	});


});


/* @param owner owner of the stocks
 * THIS FUNCTION IS NEVER USED, BUT IT SHOWS 'raw' javascript for your FYI.
*/
function showStocksTable(owner) { 
     if (owner.length == 0) { 
         document.getElementById("theTable").innerHTML = "That owner has no stocks.";
         return;
     } else {
         var xmlhttp = new XMLHttpRequest();
         xmlhttp.onreadystatechange = function() {
             if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                 document.getElementById("theTable").innerHTML = xmlhttp.responseText;
             }
         };
         xmlhttp.open("POST", "justGetStocksTable.php", true);
         xmlhttp.send();
         
     }
}