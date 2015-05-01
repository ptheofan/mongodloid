# Introduction #

Mongodloid\_Entity is class for one entry in MongoDb.

# Creating and saving #

```

$entity = new Mongodloid_Entity($id, $collection); // getting by ID
$entity = new Mongodloid_Entity(array(
   'hi' => 'there'
)); // creating
$entity->save($collection); // saving

// or
$entity = new Mongodloid_Entity(array(
   'hi' => 'there'
), $collection);
$entity->save();

// or
$entity = new Mongodloid_Entity(array(
   'hi' => 'there'
), $collection);
$entity->collection($collection);
$entity->save();

$entity->remove(); // goodbye entry!

```

# Getters and Setters #

```
$entity = new Mongodloid_Entity(array(
   'hi' => 'there',
   'i' => array(
       'am' => array(
           'an' => array('ar', 'ray')
       )
   )
);
$entity->get('hi');  // there
$entity->get('i.am.an[1]'); // ray
$entity->set('i.am.an[2]', ':-)'); // updates object in DB immediately (if it hasn't been saved, doesn't update)

```

# Atomic operations #
```
$data = array(
	'a' => 18,
	'b' => 'hello',
	'c' => array(),
	//'d' => array(),
	'e' => array(1, 2, 3),
	'f' => array(1, 2, 3),
	'g' => array(1, 2, 3),
	'h' => array(1, 2, 3)
);
				
$entity = new Mongodloid_Entity($data);	
$entity->inc('a')->inc('a', 3) // chaining!	
	   ->set('b', 'hi')
	   ->push('c', 1221)
	   ->pushAll('d', array(8, 5, 9, 6))
	   ->pop('e')
	   ->pop('f', Mongodloid_Entity::POPFIRST)
	   ->shift('f')
	   ->pull('g', 2)
	   ->pullAll('h', array(1, 3));
```

# Comparison #

```
$collection = Mongodloid_Connection::getInstance()
				->getDb('test')
				->getCollection('testcollection' . mt_rand(123, 456));
				
$entity1 = new Mongodloid_Entity(array(
	'hi' => array(1, 2, 3)
), $collection);
$entity1->save();
$entity2 = new Mongodloid_Entity(array(
	'hi' => array(1, 2, 3)
), $collection);
$entity2->save();
$entity3 = new Mongodloid_Entity($entity1->getId(), $collection);
$entity4 = new Mongodloid_Entity(array(
	'hi' => array(1, 2, 3, 4)
), $collection);
$entity4->save();

$entity1->same($entity3);  // true
$entity1->same($entity2);  // false
$entity1->same($entity4);  // false
$entity1->equals($entity3);// true
$entity1->equals($entity2);// true
$entity1->equals($entity4);// false
```

#  #