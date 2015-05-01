Mongodloid\_Cursor is a simple Iterator class, which returns [Mongodloid\_Entity](EntityClass.md)

```
$cursor = $collection->query()->cursor();
foreach($cursor as $entity) {
    // ...
}

// or even
$cursor = $collection->query();
foreach($cursor as $entity) {
    // ...
}

```