//example_AJAXformToPHP
$(document).ready(function(){
	$("#submit").click(function(){
		var name = $("#name").val();
		var email = $("#email").val();
		var password = $("#password").val();
		var contact = $("#contact").val();
		// Returns successful data submission message when the entered information is stored in database.
		var dataString = 'name1='+ name + '&email1='+ email + '&password1='+ password + '&contact1='+ contact;
		if(name==''||email==''||password==''||contact=='')
		{
			alert("Please Fill All Fields");
		}
		else
		{
			// AJAX Code To Submit Form.
			$.ajax({
				type: "POST",
				url: "example_AJAXformToPHP.php",
				data: dataString,
				cache: false,
				success: function(result){
					alert(result);
					// feeds back what the php page gives us back, into a pop-up alert (ugly)
					document.getElementById("catch-result").innerHTML = result;
				}
			});
		}
		return false;
	});
});