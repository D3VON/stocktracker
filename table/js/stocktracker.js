// unblock when ajax activity stops
$(document).ajaxStop($.unblockUI);

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
			//$.growlUI("","Fetching current quotes...");// = function(title, message, timeout, onClose)
			//$.blockUI({
			//	//message: "Fetching current quotes...",
			//	css: {
			//	border: 'none',
			//	padding: '15px',
			//	backgroundColor: '#000',
			//	'-webkit-border-radius': '10px',
			//	'-moz-border-radius': '10px',
			//	opacity: .5,
			//	color: '#fff'
			//	}
			//});
			$.ajax({
				type: "POST",
				url: "makeShowGraphsButton.php",
				data: dataString,
				cache: false,
				success: function(result){
					$("#showgraphs").html(result);
				}
			});
			$.ajax({
				type: "POST",
				url: "presentTable.php",
				data: dataString,
				cache: false,
				beforeSend: function () {
					//$(".right_con").css("min-height", "300px");
					//$(".blockOverlay").css("background-color", "");
					//$.blockUI();  //<---add this
					$.blockUI({
						//message: "Fetching current quotes...",
						css: {
							border: 'none',
							padding: '15px',
							backgroundColor: '#000',
							'-webkit-border-radius': '10px',
							'-moz-border-radius': '10px',
							opacity: .5,
							color: '#fff'
						}
					});
				},
				// THIS DOESN'T WORK FOR ME ON FIREFOX OR IE, BUT WORKS FINE IN CROME
				//complete: function () {
				//	//$(".right_con").unblock();
				//	$.unblockUI(); // <----and this
				//	$(".blockUI").fadeOut("slow"); // unblockui won't work without this additional mess
				//},
				success: function(result){
					//document.getElementById("stocktablebody").innerHTML = result;
					$("#appdiv").html(result);
					// unblock (get rid of 'loading' overlay) --See Note below
					//$('html').unblockUI();// unblockUI wasn't working in Firefox, so tried this, also didn't work.
					//alert("unblockUI should happen now");
					//$.unblockUI(); // works fine in crome, but not in Firefox or IE. (see ".always" function added to entire $.ajax below.
					//$(".blockUI").fadeOut("slow"); // unblockui won't work without this additional mess
				}
			}).always(function() { // this 'always' function is necessary for unblockUI to fire in Firefox and IE; no other way worked.
				$.unblockUI(); // <----and this
				$(".blockUI").fadeOut("slow"); // unblockui won't work without this additional mess
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
 * A. I copied the jQuery file and am serving it myself because I don't want to risk
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