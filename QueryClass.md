# Introduction #

Mongodloid\_Query is a class for building dynamical queries to DB:

```
$query = $collection->query('a', 12); // a == 12
$query = $collection->query();


$query->equals('a', 13)->greaterEq('b', 8)->mod('c', 3, 4);

// or even

$query->query('a == 13 AND b >= 8 && c % 3 == 4'); // wow!

// you can get a cursor or just use it as an iterator

foreach($query as $entity) {}

```

# Some variants (from tests) #

```
$query1 = $this->collection->query()->equals('num', 100);
$query1 = $this->collection->query('num', 100); // the same
$query1 = $this->collection->query('num == 100');
$query1 = $this->collection->query()->less('num', 100);
$query1 = $this->collection->query('num < 100');
$query1 = $this->collection->query()->lessEq('num', 100);
$query1 = $this->collection->query('num <= 100');
$query1 = $this->collection->query()->greater('num', 100);
$query1 = $this->collection->query('num > 100');
$query1 = $this->collection->query()->greaterEq('num', 100);
$query1 = $this->collection->query('num >= 100');
$query1 = $this->collection->query()->notEq('num', 100);
$query1 = $this->collection->query('num != 100');
$query1 = $this->collection->query()->in('num', array(55, 66, 77));
$query1 = $this->collection->query('num IN (99, 100, 876)');
$query1 = $this->collection->query()->notIn('num', array(55, 66, 77));
$query1 = $this->collection->query('num NOT IN (99, 100, 876)');
$query1 = $this->collection->query()->mod('num', 500, 1);
$query1 = $this->collection->query('num % 500 == 1');
$query1 = $this->collection->query()->all('gm', array(13, 14, 15));
$query1 = $this->collection->query('gm ALL (13, 14, 15)');
$query1 = $this->collection->query()->size('pf', 2);
$query1 = $this->collection->query('pf SIZE 2');
$query1 = $this->collection->query()->exists('tr.a');
$query1 = $this->collection->query('tr.a EXISTS');
$query1 = $this->collection->query()->notExists('tr.a');
$query1 = $this->collection->query('tr.a NOT EXISTS');
$query1 = $this->collection->query()->where('(this.num % 232) == this.foobar');
$query1 = $this->collection->query('WHERE (this.num % 232) == this.foobar');
$query1 = $this->collection->query(array(
			'$where' => '(this.num % 232) == this.foobar'
));
$query1 = $this->collection->query('num >= 100 && num < 200');
$query1 = $this->collection->query('num >= 100 AND num < 200');
$query1 = $this->collection->query('num >= 100 AND num < 200 and num != 150');
```