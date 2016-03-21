<?php
/**
 * Created by PhpStorm.
 * User: devonmcb
 * Date: 3/20/16
 * Time: 11:20 PM
 */



require_once('../YQL_forTesting.php');
require_once("../global.php");

$yql = new YQL_forTesting();



$dbconn = new MongoClient();
$db = $dbconn->selectDB("test");
$historyCollection = $db->history;

$findthis = array("stocklist" => 1);
$stocklist = $historyCollection->findOne($findthis);

$query = $yql->




















