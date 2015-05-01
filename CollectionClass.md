# Introduction #

Mongodloid\_Collection is a wrapper for MongoCollection class, and represent a single MongoDB collection.


# Methods #
  * `query(...)` returns [Mongodloid\_Query](QueryClass.md). If any parameters were passed, they will be passed to Mongodloid\_Query (see its documentation)
  * `save($entity)` saves [Mongodloid\_Entity](EntityClass.md)
  * `findOne($id, $want_array = false)` returns [Mongodloid\_Entity](EntityClass.md) or array of values by id
  * `update($query, $values)` is a simple update. `$query` and `$values` must be in `MongoDB` standard for `update()` format. For more usable updates, use [Mongodloid\_Entity](EntityClass.md)
  * `remove($query)` or `remove($id)` or `remove($entity)` removes entities from collection
  * `count()` returns count of elements in collection
  * `drop()` drops collection
  * `clear()` clears collection
  * `getName()` returns collection name
  * `find($query)` returns MongoCursor. Don't use it, try `query()` function instead. You'll like it!

## Indexes ##
  * `dropIndexes()` deletes all indexes
  * `dropIndex($index)` deletes one index. `$index` is a name of a row or an array like `array('field' => 1, 'field2' => -1)`
  * `ensureIndex($field, $param = null)` creates an index, if it doesn't exists yet. `$field` is a field name or an array, which keys are field names and values are index directions. `$param` can be `null`, `self::UNIQUE` or `self::DROP_DUPLICATES`
  * `ensureUniqueIndex($field, $dropDups = false)` is an alias for `ensureIndex($field, self::UNIQUE)`
  * `getIndexedFields()` returns array of all indexed fields, like `array('field1', 'field2')`
  * getIndexes() returns array of indexes in MongoDB index description format