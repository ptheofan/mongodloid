# Introduction #

Mongoloid\_DB is a wrapper for MongoDB class, and represent a single MongoDB database.

# Details #

Currently it has only three methods:

  * `Mongodloid_DB::__construct` is something you don't need, use `Mongodloid_Connection::getDb()` instead (see below)
  * `Mongodloid_DB::getCollection($name)` returns [collection](CollectionClass.md) by name. Notice: links to collections are cached to private variable.
  * `Mongodloid_DB::getName()` returns name of the database.

# Examples #

```
<?php

$connection = Mongodloid_Connection::getInstance();

$db = $connection->getDb('test');

echo 'Whoa, I haz ', $db->getName(), ' database!', "\n";

$collection = $db->getCollection('supercollection'); 

```