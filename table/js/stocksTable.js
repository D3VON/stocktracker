		


$(document).ready(function() {
	
	//ActivateFloatingHeaders("table.stockstable");
	/* trying to fix header of table to not scroll away 
	 * this shit won't work at all.  Can't pin down why.

	//ActivateFloatingHeaders("table.stockstable");
	var $table = $('table.stockstable');
	$table.floatThead();
	  * /
	$('table.stockstable').floatThead({
		position: 'fixed'
	});
	/* */
	
	/* This works like a dream: it sorts the table by column (if a column has
	 * the class tag 'sortable', and it toggles between ascending and descending
	 * with very minimal code. 
	 * 
	 * found it here: http://jsfiddle.net/gQNPt/153/
	 * 
	 * PROBLEM: 'numeric' columns with $, %, or commas sort wrong. 
	 * added regex to remove $,%,comma before sorting
	 */
	$(".sortByColumn").click(function(){
		//query this tag, prepare temp var for swapping asc/desc
	    var scend = $(this).hasClass('asc') ? 'desc' : 'asc';
	    
	    // get rid of selector asc or desc
	    $('.sortByColumn').removeClass('asc').removeClass('desc');
	    
	    // add a 'fresh' selector stored in 'scend' variable
	    $(this).addClass(scend);
	    
	    // Get all preceding siblings of each element in the set like this one,
	    // (I guess that's like an array), and just get that array's count, 
	    // that way, we get which column number this is (starting with the zeroth)
	    var colIndex = $(this).prevAll().length;
	    var tbod = $(this).closest("table").find("tbody");
	    var rows = tbod.find("tr");
	    
	    // looks similar to PHP's usort built-in function that needs how you're basing your sort.
	    rows.sort(function(a,b){
	        var A = $(a).find("td").eq(colIndex).text();
	        var B = $(b).find("td").eq(colIndex).text();
			/* Have to parse for columns of numberic data that's in the DOM with $,%,or commas
			 * or it screws up the sorting (2000 comes before 3 when sorted asc like text)
			 * If a field begins with $, or ends with %, 
			 * strip out those, but keep the + or minus sign.
			 */          // note: really only need a, not be also
			if ('$' == A.charAt(0)  || '%' == A.substr(A.length - 1) ) {
		        A = Number(A.replace(/[^0-9\.\+-]+/g,""));
		        B = Number(B.replace(/[^0-9\.\+-]+/g,""));
			}
	        if (!isNaN(A)) A = Number(A);
	        if (!isNaN(B)) B = Number(B);
	        
	        return scend == 'asc' ? A > B : B > A;
	    });
	   
	    
	    // I guess this is like a foreach loop
	    // I don't get how it works: looks like it's just making the table bigger, but
	    // I'm assuming it has obliterated the original table body somewhere above here
	    $.each(rows, function(index, ele){
	        tbod.append(ele);
	    });
	});
	
	/* Since sort is broken for columns of numberic data dressed up with $,%,or commas
	 * We have to compensate here. If a field begins with $, or ends with %, 
	 * strip out those, but keep the + or minus sign. 
	if ('$' === A.charAt(0) ) || ('%' === A.substr(A.length - 1) ) {
        var Astuff = $(a).find("td").eq(colIndex).text();
        var A = Number(Astuff.replace(/[^0-9\.]+/g,""));
        var Bstuff = $(b).find("td").eq(colIndex).text();
        var B = Number(Bstuff.replace(/[^0-9\.]+/g,""));
	}else{
        var A = $(a).find("td").eq(colIndex).text();
        var B = $(b).find("td").eq(colIndex).text();
	}
	 */
	

	
	/* This works like a dream: it sorts the table by column (if a column has
	 * the class tag 'sortable', and it toggles between ascending and descending
	 * with very minimal code. 
	 * 
	 * PROBLEM: 'numeric' columns with $, %, or commas sort wrong. 
	 * I glanced at having CSS format them, but, ugh.  
	 */
	$(".sortbyNumber").click(function(){
		//query this tag, prepare temp var for swapping asc/desc
	    var scend = $(this).hasClass('asc') ? 'desc' : 'asc';
	    
	    // get rid of selector asc or desc
	    $('.sortbyNumber').removeClass('asc').removeClass('desc');
	    
	    // add a 'fresh' selector stored in 'scend' variable
	    $(this).addClass(scend);
	    
	    // Get all preceding siblings of each element in the set like this one,
	    // (I guess that's like an array), and just get that array's count, 
	    // that way, we get which column number this is (starting with the zeroth)
	    var colIndex = $(this).prevAll().length;
	    var tbod = $(this).closest("table").find("tbody");
	    var rows = tbod.find("tr");
	    
	    // looks similar to PHP's usort built-in function that needs how you're basing your sort.
	    rows.sort(function(a,b){
	    	// strip out $, %, and comma if it's that kind of data 
	    	// (so it sorts correctly as purely numeric, not as text)
	        var Astuff = $(a).find("td").eq(colIndex).text();
	        var A = Number(Astuff.replace(/[^0-9\.\+-]+/g,""));
	        
	        var Bstuff = $(b).find("td").eq(colIndex).text();
	        var B = Number(Bstuff.replace(/[^0-9\.\+-]+/g,""));

	        if (!isNaN(A)) A = Number(A);
	        if (!isNaN(B)) B = Number(B);
	        
	        return scend == 'asc' ? A > B : B > A;
	    });
	    
	    // I guess this is like a foreach loop
	    // I don't get how it works: looks like it's just making the table bigger, but
	    // I'm assuming it has obliterated the original table body somewhere above here
	    $.each(rows, function(index, ele){
	        tbod.append(ele);
	    });
	});
	
	
	
	
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
	

	/* Range slider used to show 52 week high, low, last. 
	 * Not used to select anything, only used as a graphic.
	 * NOT USED DUE TO DIFFICULTY WITH PURCH MIGHT BE ABOVE OR BELOW 52WK HIGH/LOW
	 * (NEED MORE LOGIC TO PULL THAT OFF)
	//$(function() {
	    $( "#slider-range" ).slider({
	      range: true,
	      min: 0,   // 52 week low
	      max: 500, // 52 week high
	      values: [ 75, 300 ],  // purchase, current
	      slide: function( event, ui ) {
	        $( "#amount" ).val( "$" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
	      }
	    });
	    $( "#amount" ).val( "$" + $( "#slider-range" ).slider( "values", 0 ) +
	      " - $" + $( "#slider-range" ).slider( "values", 1 ) );
	//});

	 */

});
/******************************************************************************
 * END jQuery
 * ****************************************************************************
 */







/*  I was trying to figure out how to toggle rows...left this artifact to illustrate that:
 * if I leave this here, outside of jQuery above, the add and edit pop-up overlays for the 
 * table WILL APPEAR UNDER THE TABLE, AND NOT BE OVERLAYS. In other words, if this code is
 * active here, it will screw up the jQuery that handles those buttons. 
 */
/*
function sortColumn(strategy) { // could literally try to use the strategy pattern
     if (strategy == 'name_ascend') { 
    	 alert("woof");
     }elseif(strategy == 'name_descend'){
    	 alert("meow");
     }
    /*	 
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
   */














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