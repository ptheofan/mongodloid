After [Installation](Installation.md), just get the `$connection` object like this:

```
$connection = Mongodloid_Connection::getInstance(); 
```

(It's a singleton, so you can run `Mongodloid_Connection::getInstance();` again and again.)

To get a DB and collection object, try
```
$db = $connection->getDb('test');                      // <- Mongodloid_DB
$collection = $db->getCollection('supercollection');   // <- Mongodloid_Collection
```

Any links to the dbs and collections is cached, so feel free to use `->getDb()` and `->getCollection()` as much as you want to.

  * [Mongodloid\_DB Class](DBClass.md)
  * [Mongodloid\_Collection Class](CollectionClass.md)
  * [Mongodloid\_Query Class](QueryClass.md)
  * [Mongodloid\_Entity Class](EntityClass.md)
  * [Mongodloid\_Cursor Class](CursorClass.md)

For further information, see [tests/Mongodloid](http://code.google.com/p/mongodloid/source/browse/#hg/tests/Mongodloid) folder. There are the latest use-cases, functions, etc.