[Source](http://bitbucket.org/va1en0k/mongodloid/) is now on [BitBucket](http://bitbucket.org/va1en0k/mongodloid/)!
So you can simply go there and get all the latest library code in .gz or .zip or whatever, just check the link "get source" on the Mongodloid's [BitBucket page](http://bitbucket.org/va1en0k/mongodloid/get/240df04ab7cc.gz).

Another good news: [Zend Framework proposal](http://framework.zend.com/wiki/display/ZFPROP/Zend_Nosql_Mongo+-+Valentin+Golev) to include something much greater than this library into Zend Framework. Yes comments please!

# Warning! #

Since I'm planning to add MongoDB support to [Zend Framework](http://framework.zend.com/), I will not add any new features to Mongodloid except ones I need by myself (currently I'm using Mongodloid in production).

But, I still commit all changes and bug fixes to [Mercurial](http://bitbucket.org/va1en0k/mongodloid/get/240df04ab7cc.gz), so you can get the latest  version and use it as much as you want. If you find any bugs or want to submit a patch, just send me an e-mail (mongodloid@va1en0k.net) or create an Issue.

Thank you!


# What is this? #

Tired of low-level work with '$dte's? Try the Mongodloid out! :) (Be careful, it can be not very stable now)

There's a greater, but still not documented version in repository. If you can, I recommend you to check it out (simply click "get source" on [BitBucket Page](http://bitbucket.org/va1en0k/mongodloid/get/240df04ab7cc.gz)) rather then downloading 0.0.3. Of course, it's full backwards compatible.

Dowloands and Wiki will be updated soon.

# Atomic operations #
```
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

Since [Revision 0547418056](https://code.google.com/p/mongodloid/source/detail?r=0547418056), it's better to do


```
$entity->startUpdate()->inc('a')->inc('a', 3) 
           ->set('b', 'hi')->endUpdate();
```
because it produces only one request instead of 3. And it has some transactional style: while you don't call endUpdate(), no changes are written.



# Queries #
```
$query = $collection->query('a', 12); // a == 12
$query = $collection->query();


$query->equals('a', 13)->greaterEq('b', 8)->mod('c', 3, 4);

// or even

$query->query('a == 13 AND b >= 8 && c % 3 == 4'); // wow!

foreach($query as $entity) {}
```

# Great getters and setters #
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
$entity->set('i.am.an[2]', ':-)'); 
```