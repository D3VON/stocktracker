
$(document).ready(function() {

	// on button click, present that owner's table (send to that div)
	//$('#theOwnerButton').click(function(e){	//works in the .php version, but not in the .html version. :^(
	$("#ownerForm").on("submit", function (e) {	//works in both .php and .html version.

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
			// 'loading' overlay while data is being fetched. --See Note 1 below
			$.blockUI({ css: {
				border: 'none',
				padding: '15px',
				backgroundColor: '#000',
				'-webkit-border-radius': '10px',
				'-moz-border-radius': '10px',
				opacity: .5,
				color: '#fff'
			} });
			$.ajax({
				type: "POST",
				url: "presentTable.php",
				data: dataString,
				cache: false,
				success: function(result){
					//document.getElementById("stocktablebody").innerHTML = result;
					$("#thetable").html(result);
					// unblock (get rid of 'loading' overlay) --See Note 1 below
					$.unblockUI();
					$(".blockUI").fadeOut("slow"); // unblockui won't work without this additional mess
				}
			});
			$.ajax({
				type: "POST",
				url: "makeShowGraphsButton.php",
				data: dataString,
				cache: false,
				success: function(result){
					$("#showgraphs").html(result);
				}
			});
		}
	});
});


/* Notes
 *
 * 'loading' overlay.
 * That came from http://malsup.com/jquery/block/  See demos at http://jquery.malsup.com/block/#demos
 * (Other cool variations and options available there.)
 * Steps:
 * A. I copied the unique jQuery file and am serving it myself because I don't want to risk
 * refer to that web site in case it's updated and breaks my code. So I added this
 * to the HTML page: <script src="js/jquery.blockUI.js"></script>
 * B. In the jQuery (in this file)
 * $("#ownerForm").on("submit"....
 * ...is where I call the 'loading' overlay before it starts working on the AJAX call
 * that makes the table.
 * C. When the AJAX call finishes, and loads the table to that div, only then do I
 * call
 * $.unblockUI();
 * $(".blockUI").fadeOut("slow");
 * Uncomfortably, I can't just rely on the single $.unblockUI();
 * ...but I have to also call $(".blockUI").fadeOut("slow");
 * ...or else the overlay won't go away.  Looks messy to me, but I'm not going to waste more time figuring it out.
 * Another problem I had was adding a message. You can google to see how to do it, but when I did it,
 * it either wouldn't work, or the text size and color got all messed up.  So I just kept the default 'please wait'.
 *
 *
 *
 *
 *
 * */