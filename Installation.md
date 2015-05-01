Just unpack or checkout all Mongodloid files from library folder anywhere, then add to your script:

```
require_once 'your/path/to/library/Mongodloid/Connection.php'.
$connection = Mongodloid_Connection::getInstance();
```

For further instructions (what to with the $connection now), check [Using](Using.md).