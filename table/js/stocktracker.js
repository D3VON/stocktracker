
$(document).ready(function() {

	// on button click, present that owner's table (send to that div)
	//$('#theOwnerButton').click(function(e){	//works in the .php version, but not in the .html version. :^(
	$("#ownerForm").on("submit", function (e) {	//works in bith .php and .html version. 
		e.preventDefault();
		var owner = $("#ownerName").val();
		//alert("owner is: " + owner);
		//$("#ownerName").html(owner);
		var dataString = '&owner='+ owner;
   		if(owner=='')
   		{
   			alert("Please indicate which owner.  Thank you.");
   		}
   		else
   		{
   			$.ajax({
   				type: "POST",
   				url: "presentTable.php",
   				data: dataString,
   				cache: false,
   				success: function(result){
   					//document.getElementById("stocktablebody").innerHTML = result;
   					$("#thetable").html(result);
   				}
   			});
   		}
	});
});	