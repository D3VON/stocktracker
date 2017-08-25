$(document).ready(function() {


  /* This works like a dream: it sorts the table by column (if a column has
   * the class tag "sortable", and it toggles between ascending and descending
   * with very minimal code. 
   * 
   * found it here: http://jsfiddle.net/gQNPt/153/
   * 
   * PROBLEM: "numeric" columns with $, %, or commas sort wrong.
   * added regex to remove $,%,comma before sorting
   */
  $(".sortByColumn").on("click",function(){
    //query this tag, prepare temp var for swapping asc/desc
      var scend = $(this).hasClass("asc") ? "desc" : "asc";

      // get rid of selector asc or desc
      $(".sortByColumn").removeClass("asc").removeClass("desc");
      
      // add a "fresh" selector stored in "scend" variable
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
      if ("$" === A.charAt(0)  || "%" === A.substr(A.length - 1) ) {
            A = Number(A.replace(/[^0-9\.\+\-]+/g,""));
            B = Number(B.replace(/[^0-9\.\+\-]+/g,""));
      }
          if (!isNaN(A)) { A = Number(A); }
          if (!isNaN(B)) { B = Number(B); }
          
          return (scend === "asc") ? A > B : B > A;
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
  if ("$" === A.charAt(0) ) || ("%" === A.substr(A.length - 1) ) {
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
   * the class tag "sortable", and it toggles between ascending and descending
   * with very minimal code. 
   * 
   * PROBLEM: "numeric" columns with $, %, or commas sort wrong.
   * I glanced at having CSS format them, but, ugh.  
   */
  $(".sortbyNumber").on("click",function(){
    //query this tag, prepare temp var for swapping asc/desc
      var scend = $(this).hasClass("asc") ? "desc" : "asc";
      
      // get rid of selector asc or desc
      $(".sortbyNumber").removeClass("asc").removeClass("desc");
      
      // add a "fresh" selector stored in "scend" variable
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
          var A = Number(Astuff.replace(/[^0-9.+\-]+/g,""));
          
          var Bstuff = $(b).find("td").eq(colIndex).text();
          var B = Number(Bstuff.replace(/[^0-9.\+\-]+/g,""));

		  if (!isNaN(A)) { A = Number(A); }
		  if (!isNaN(B)) { B = Number(B); }
          
          return (scend === "asc") ? A > B : B > A;
      });
      
      // I guess this is like a foreach loop
      // I don't get how it works: looks like it's just making the table bigger, but
      // I'm assuming it has obliterated the original table body somewhere above here
      $.each(rows, function(index, ele){
          tbod.append(ele);
      });
  });
  
  
  
  
  // opens a pop-up form as an overlay (id: #the_add_form) that has its own buttons
  $("#addy").on("click",function(e){
    //e.preventDefault();
      $("#the_add_form").dialog("open");
  });

  $( "#datepicker,#editdatepicker" ).datepicker(
      {altFormat: "yy-mm-dd"}
  );
  
  
  
  $("#the_edit_form").dialog({
      autoOpen: false,
      height: 380,
      width: 400,
      modal: true,
      buttons: [
        {
          text: "Cancel",
            click: function() {
				"use strict";
                $(this).dialog("close");
            }
          },
        {
              text: "Submit",
            click: function() {
               "use strict";
               var symbol = $("#editsymbol").val();
               var quant = $("#editquant").val();
               var price = $("#editprice").val();
               var datepicker = $("#editdatepicker").val();
               var fee = $("#editfee").val();
               var account = $("#editaccount").val();
               var owner = $("#editowner").val();
               //alert("owner is: " + owner);
               var id = $("#editid").val();
               // Returns successful data submission message when the entered information is stored in database.
               var dataString = "symbol="+ symbol + "&quant="+ quant + "&price="+ price + "&datepicker="+ datepicker + "&fee="+ fee + "&account="+ account + "&owner="+ owner + "&id=" + id;
               if(symbol===""||quant===""||price===""||datepicker===""||fee===""||account===""||owner==="")
               {
                 alert("Please Fill All Fields");
               }
               else
               {
            // "loading" overlay while data is being fetched. --See Note 1 below
            $.blockUI({ css: {
              border: "none",
              padding: "15px",
              backgroundColor: "#000",
              "-webkit-border-radius": "10px",
              "-moz-border-radius": "10px",
              opacity: 0.5,
              color: "#fff"
            } });
                 $.ajax({
                   type: "POST",
                   url: "editPurch.php",
                   data: dataString,
                   cache: false,
                   success: function(result){
                     //document.getElementById("stocktablebody").innerHTML = result;
                //$("#appdiv").html(result);
                //$("#stockstable tbody").html(result);
                $("#stockstable").html(result);
                // unblock (get rid of "loading" overlay) --See Note 1 below
                $.unblockUI();
                $(".blockUI").fadeOut("slow"); // unblockui won't work without this additional mess
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

  // This is where the buttons of the form are defined.
  // .. and the actions are defined.
  $("#the_add_form").dialog({
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
               var dataStringForAdd = "symbol="+ symbol + "&quant="+ quant + "&price="+ price + "&datepicker="+ datepicker + "&fee="+ fee + "&account="+ account + "&owner="+ owner;
          var dataStringForHistory = "symbol="+ symbol;

               if(symbol===""||quant===""||price===""||datepicker===""||fee===""||account===""||owner==="")
               {
                 alert("Please Fill All Fields<br>" + dataString + "<br>");
               }
               else
               {
            // "loading" overlay while data is being fetched. --See Note 1 below
            $.blockUI({ css: {
              border: "none",
              padding: "15px",
              backgroundColor: "#000",
              "-webkit-border-radius": "10px",
              "-moz-border-radius": "10px",
              opacity: .5,
              color: "#fff"
            } });
                 $.ajax({
                   type: "POST",
                   url: "addPurch.php",
                   data: dataStringForAdd,
                   cache: false,
                   success: function(result){
                     //document.getElementById("stocktablebody").innerHTML = result;
                //$("#appdiv").html(result);
                $("#stockstable").html(result);
                // unblock (get rid of "loading" overlay) --See Note 1 below
                $.unblockUI();
                $(".blockUI").fadeOut("slow"); // unblockui won't work without this additional mess
              }
                 });
            $.ajax({
              type: "POST",
              url: "addHistory.php",
              data: dataStringForHistory,
              cache: false,
              success: function(result){
                //alert("Message back from addHistory.php:" + result);
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

  //$("tbody tr").on("click",function() {// same effect; won't work on Firefox or IE, but fine on Chrome.
  $("tbody").on("click","tr",function() {
    console.log("clicked");
    $(this).addClass("highlight").siblings().removeClass("highlight");
  });


  $("#selectAll").on("click",function(event){
      $(this).closest("table").find("td input:checkbox").prop("checked", this.checked);
  });

  $("#removie").on("click",function(event){
    //alert("we're in the jQuery #removie function.  Here's the remove array: " + $("input[name="remove[]"]").val());
    event.preventDefault();
    var remove = [];
    //var jsonString = JSON.stringify(dataArray);
    
    //stackoverflow.com/questions/24335862/how-i-get-array-checkbox-value-in-jquery
    $(":checkbox:checked").each(function(i){
      remove[i] = $(this).val();
    });
    //$("input[name="remove[]"]").val();
    //alert("we're in the jQuery #removie function.  Here's the remove array: " + remove);  
    
    
    var owner = $("#owner").val();
    var dataString = "remove="+ remove + "&owner="+ owner;
    //var url = "removePurch.php"; //$(this).attr("action");// this attribute's action (specified in <form> tag)

    // "loading" overlay while data is being fetched. --See Note 1 below
    $.blockUI({ css: {
      border: "none",
      padding: "15px",
      backgroundColor: "#000",
      "-webkit-border-radius": "10px",
      "-moz-border-radius": "10px",
      opacity: .5,
      color: "#fff"
    } });
    //alert("we're in the jQuery #removie function.  Here's the dataString: " + dataString);
    $.ajax({
      type: "POST",
      url: "removePurch.php",
      data: dataString,
      cache: false,
      success: function(result) {
        //document.getElementById("stocktablebody").innerHTML = result;
        // don't do this: $("#appdiv").html(result); ...works well just replacing the body
        $("#stockstable").html(result);
        // unblock (get rid of "loading" overlay) --See Note 1 below
        $.unblockUI();
        $(".blockUI").fadeOut("slow"); // unblockui won't work without this additional mess
      }
    });
    return false;
  });



  /*
   This will show the table head when the user scrolls down far enough to hide the
   original table head. It will hide again when the user has scrolled the page up
   far enough again. Found this on stackoverflow http://stackoverflow.com/questions/4709390/table-header-to-stay-fixed-at-the-top-when-user-scrolls-it-out-of-view-with-jque
   (answer from member named entropy)
   Copied code from http://jsfiddle.net/cjKEx/

   He says: "I was trying to obtain the same effect without resorting to fixed size columns
   or having a fixed height for the entire table.

   "The solution I came up with is a hack. It consists of duplicating the entire table then
   hiding everything but the header, and making that have a fixed position."

   PROBLEMS: (so far, it's pretty damned good!)
   1. just css: need to make borders btwn cells not disappear.
   2. more importantly, all the jQuery no longer works unless you scroll back to the top.
   */
  function moveScroll(){
    var scroll = $(window).scrollTop();  //Get the current vertical position of the scroll bar for the element
    // basically the top of the window    The vertical scroll position is the same as the number of pixels that are hidden from view above the scrollable area. If the scroll bar is at the very top, or if the element is not scrollable, this number will be 0.
    // so if the value of scroll gets greater than the value of anchor_top, do that stuff
    // likewise, if the value of scroll gets greater than anchor_bottom, stop doing that stuff

    var anchor_top = $("#stockstable").offset().top;
    var anchor_bottom = $("#bottom_anchor").offset().top;
    if (scroll>anchor_top && scroll<anchor_bottom) {

      // weird syntax.  It looks like it's setting up an empty id'd tag to be filled in a couple lines later.
      clone_table = $("#clone");
      if(clone_table.length === 0){
        clone_table = $("#stockstable").clone();
        clone_table.attr("id", "clone");
        clone_table.css({position:"fixed",
          "pointer-events": "none",
          top:0});
        clone_table.width($("#stockstable").width());
        $("#appdiv").append(clone_table);
        $("#clone").css({visibility:"hidden"});
        $("#clone thead").css({visibility:"visible", "pointer-events":"auto"});
      }
    } else {
      $("#clone").remove();
    }
  }
  $(window).scroll(moveScroll);//call moveScroll each time the scroll event is triggered.

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
     if (strategy == "name_ascend") {
       alert("woof");
     }elseif(strategy == "name_descend"){
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
 * THIS FUNCTION IS NEVER USED, BUT IT SHOWS "raw" javascript for your FYI.
*/
// function showStocksTable(owner) {
//      if (owner.length === 0) {
//          document.getElementById("theTable").innerHTML = "That owner has no stocks.";
//      } else {
//          var xmlhttp = new XMLHttpRequest();
//          xmlhttp.onreadystatechange = function() {
//              if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
//                  document.getElementById("theTable").innerHTML = xmlhttp.responseText;
//              }
//          };
//          xmlhttp.open("POST", "justGetStocksTable.php", true);
//          xmlhttp.send();
//
//      }
// }