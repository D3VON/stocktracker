
    $('#box_form').dialog({
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
		       		if(symbol==''||quant==''||price==''||datepicker==''||fee==''||account=='')
		       		{
		       			alert("Please Fill All Fields");
		       		}
		       		else
		       		{
		       			// AJAX Code To Submit Form.
		       			$.ajax({
		       				type: "POST",
		       				url: "addPurch.php",
		       				data: dataString,
		       				cache: false,
		       				success: function(result){
		       					//alert(result);
		       					// feeds back what the php page gives us back, into a pop-up alert (ugly)
		       					document.getElementById("thetable").innerHTML = result;
		       				}
		       			});
		       			//close this little input form overlay
			       		$( this ).dialog( "close" );
		       		}
		       		return false;
            
		            //$('#addsymbol').submit(); // was in orig. code I copied; replaced with above
		            // .......because it wasn't forwarding the POST data to the php file.
	           	}
           	}
        ]
    });
    // opens a little form (id: #box_form) that has its own buttons, which are coded just above here.
    $('#addy').click(function(e){
    //$('#addy').button().click(function(e){
        $('#box_form').dialog('open');
    });