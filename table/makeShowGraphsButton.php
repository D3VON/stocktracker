<?php
/**
 * Created by PhpStorm.
 * User: devonmcb
 * Date: 4/5/16
 * Time: 4:15 PM
 */



    $owner = $_POST['owner'];

    if($owner == "" || !ctype_alnum($owner) ){
        exit; // error message will be generated elsewhere: presentTable.php called at the same time as this script
    }else {

        // static html link -- URL GET value for $owner will have to be checked for possible tampering

        $pieceOfHTML = "<a  href=\"../graphstocks.php?owner=$owner\">"
            . "<img src=\"../table/css/images/graphicon.png\" alt=\"show graphs for $owner\"/>"
            . "</a>";

        echo $pieceOfHTML;
    }

    // js way   This will not work: data isn't flowing to the final destination div for some reason. 
/*
$jsButton  = "<form id=\"graphForm\">";
$jsButton .= "    <input type=\"hidden\" name=\"owner\" id=\"owner\" value=\"$owner\" />";
$jsButton .= "    <input  type=\"submit\" id=\"graphbutton\" value=\"show graphs for $owner\"></input>";
$jsButton .= "    Can't get this button to work the javascripty way I want it to.  Will make it static instead. Tomorrow. ";
$jsButton .= "</form>";

$jQueryFunction = <<<JQUERYREADY
<script>
$(document).ready(function() {
    $("#graphForm").on("submit", function (e) {	//works in both .php and .html version.
        e.preventDefault();
        var owner = $("#owner").val();
        // TEST: alert("owner is: " + owner);
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
				url: "graphStocks.php",
				data: dataString,
				cache: false,
				success: function(result){
            $("#appdiv").html(result);
            // unblock (get rid of 'loading' overlay) --See Note 1 below
            $.unblockUI();
            $(".blockUI").fadeOut("slow"); // unblockui won't work without this additional mess
        }
			});
		}
    });
});
</script>

JQUERYREADY;

$jsButton .= $jQueryFunction;
    echo $jsButton;

*/ // end Javascript style which was a failure (owing to how rickshaw loads asynchronously -- seems like the asynch chain was too long for it to achieve showing up on the app page.

