<?php
/**
 * Created by PhpStorm.
 * User: devon
 * Date: 5/19/15
 * Time: 2:11 AM
 *
 * Basic MongoDB functions -- nothing to do with real data
 *
 *
 */





/* this section: add to mongodb
$this->dbconn = new MongoClient("mongodb://${username}:${password}@localhost/myDatabase");
$m = new MongoClient();
$db = $dbconn->selectDB("test");
// select a collection (analogous to a relational database's table)
$collection = $db->stocks;

// add a record
$document = array( "title" => "Calvin and Hobbes", "author" => "Bill Watterson" );
$collection->insert($document);

// add another record, with a different "shape"
$document = array( "title" => "XKCD", "online" => true );
$collection->insert($document);

// find everything in the collection
$cursor = $collection->find();

// iterate through the results
foreach ($cursor as $document) {
    echo $document["title"] . "\n";
}


*/


?>