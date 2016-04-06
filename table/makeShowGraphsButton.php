<?php
/**
 * Created by PhpStorm.
 * User: devonmcb
 * Date: 4/5/16
 * Time: 4:15 PM
 */


	$owner = $_POST['owner'];

// YIKES: this is super brittle with all the hard-coding of paths.  Yuck.
    $pieceOfHTML = "<a  href=\"../graphstocksfrommongo.php?owner=$owner\">"
                 . "<img src=\"../table/css/images/graphicon.png\" alt=\"show graphs for this user\"/>"
                 . "</a>";

    echo $pieceOfHTML;
