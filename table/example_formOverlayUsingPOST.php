<?php

echo "woof";
echo "<BR>".$_POST['symbol']."<br>";
echo "<br>here is var_dump of POST:<br>";
var_dump($_POST);
// POST SHOULD BE RIGOROUSLY FILTERED TO PREVENT ANY KIND OF WEIRD INJECTION SITUATION

?>