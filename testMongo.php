<?php
/**
 * Created by PhpStorm.
 * User: devon
 * Date: 5/19/15
 * Time: 2:11 AM
 */


$this->dbconn = new MongoClient("mongodb://${username}:${password}@localhost/myDatabase");



// connect
$m = new MongoClient();

// select a database
$db = $m->comedy;

// select a collection (analogous to a relational database's table)
$collection = $db->cartoons;

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





?>