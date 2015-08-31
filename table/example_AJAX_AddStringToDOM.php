<?php // example_AJAX_AddStringToDOM.php


/*
  Just a 'dummy' script to imitate 
  1) storing new data;
  2) returning existing data plus the new data.
  
  Instead of reading/updating a database, it uses a local text file. 
  
  Instead of real data, it just does a time-stamp/date.
  
 */ 
  
	/* Open a file for read/write. The existing data in file is preserved. 
	File pointer starts at the end of the file. Creates a new file if the file doesn't exist
	 */
	$myfile = fopen("example_AJAX_AddStringToDOM.txt", "a+") or die("Unable to open file!");
	$theDate = date("Y-m-d");
	fwrite($myfile, "$theDate\n");
	
	$stuff = explode("\n", file_get_contents("example_AJAX_AddStringToDOM.txt"));
	//print_r($stuff);
	//echo "<br>----------------------------------------------<br>";
	
	fclose($myfile);
	
	$dramaticEffect = "";
	$result = "<table>";
	foreach($stuff as $item){
		$result .= "<tr>$dramaticEffect<td>$item</td></tr>";
		$dramaticEffect .= "<td></td>";
	}
	$result .= "</table>";
	
	echo $result;	  
  
  ?>