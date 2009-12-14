<?php
require_once '..\library\Mongodloid\Connection.php';

class QueryTest extends PHPUnit_Framework_TestCase 
{
	public function testArrayAccess() {
		$cursor = $this->collection->query()->less('num', 100)->cursor();
		$this->assertEquals($cursor->count(), 100);
		for ($i = 0; $i < count($cursor); $i++) {
			$this->assertThat($cursor[$i]->get('num'), $this->lessThan(100));
		}
	}
	public function testSkipLimit() {
		$query1 = $this->collection->query()->less('num', 100);
		$this->assertEquals($query1->count(), 100);
		$query1->skip(10);
		$query1->limit(10);
		$i = 0;
		foreach($query1 as $c) $i++;
		$this->assertEquals(10, $i);
	}
	public function testCondit() {
		$query1 = $this->collection->query()->equals('num', 100);
		$this->assertEquals($query1->count(), 1);
		
		$query1 = $this->collection->query('num', 100); // the same
		$this->assertEquals($query1->count(), 1);
		
		$query1 = $this->collection->query('num == 100');
		$this->assertEquals($query1->count(), 1);
		
		$query1 = $this->collection->query()->less('num', 100);
		$this->assertEquals($query1->count(), 100);
		
		$query1 = $this->collection->query('num < 100');
		$this->assertEquals($query1->count(), 100);
		
		$query1 = $this->collection->query()->lessEq('num', 100);
		$this->assertEquals($query1->count(), 101);
		
		$query1 = $this->collection->query('num <= 100');
		$this->assertEquals($query1->count(), 101);
		
		$query1 = $this->collection->query()->greater('num', 100);
		$this->assertEquals($query1->count(), 899);
		
		$query1 = $this->collection->query('num > 100');
		$this->assertEquals($query1->count(), 899);
		
		$query1 = $this->collection->query()->greaterEq('num', 100);
		$this->assertEquals($query1->count(), 900);
		
		$query1 = $this->collection->query('num >= 100');
		$this->assertEquals($query1->count(), 900);
		
		$query1 = $this->collection->query()->notEq('num', 100);
		$this->assertEquals($query1->count(), 999);
		
		$query1 = $this->collection->query('num != 100');
		$this->assertEquals($query1->count(), 999);

		$query1 = $this->collection->query()->in('num', array(55, 66, 77));
		$this->assertEquals($query1->count(), 3);
		
		$query1 = $this->collection->query('num IN (99, 100, 876)');
		$this->assertEquals($query1->count(), 3);
		
		$query1 = $this->collection->query()->notIn('num', array(55, 66, 77));
		$this->assertEquals($query1->count(), 997);
		
		$query1 = $this->collection->query('num NOT IN (99, 100, 876)');
		$this->assertEquals($query1->count(), 997);
		
		$query1 = $this->collection->query()->mod('num', 500, 1);
		$this->assertEquals($query1->count(), 2);
		
		$query1 = $this->collection->query('num % 500 == 1');
		$this->assertEquals($query1->count(), 2);
		
		$query1 = $this->collection->query()->all('gm', array(13, 14, 15));
		$this->assertEquals($query1->count(), 5);
		
		$query1 = $this->collection->query('gm ALL (13, 14, 15)');
		$this->assertEquals($query1->count(), 5);
		
		$query1 = $this->collection->query()->size('pf', 2);
		$this->assertEquals($query1->count(), 2);
		
		$query1 = $this->collection->query('pf SIZE 2');
		$this->assertEquals($query1->count(), 2);
		
		$query1 = $this->collection->query()->exists('tr.a');
		$this->assertEquals($query1->count(), 2);
		
		$query1 = $this->collection->query('tr.a EXISTS');
		$this->assertEquals($query1->count(), 2);
		
		$query1 = $this->collection->query()->notExists('tr.a');
		$this->assertEquals($query1->count(), 998);
		
		$query1 = $this->collection->query('tr.a NOT EXISTS');
		$this->assertEquals($query1->count(), 998);
		
		$query1 = $this->collection->query()->where('(this.num % 232) == this.foobar');
		$this->assertEquals($query1->count(), 1000);
		
		$query1 = $this->collection->query('WHERE (this.num % 232) == this.foobar');
		$this->assertEquals($query1->count(), 1000);
		
		$query1 = $this->collection->query(array(
			'$where' => '(this.num % 232) == this.foobar'
		));
		$this->assertEquals($query1->count(), 1000);
	}
	public function testBoolean() {
		$query1 = $this->collection->query('num >= 100 && num < 200');
		$this->assertEquals($query1->count(), 100);
		
		$query1 = $this->collection->query('num >= 100 AND num < 200');
		$this->assertEquals($query1->count(), 100);
		
		$query1 = $this->collection->query('num >= 100 AND num < 200 and num != 150');
		$this->assertEquals($query1->count(), 99);
	}
	public function testIterator() {
		$query1 = $this->collection->query('num >= 100 && num < 200');
		$c = 0;
		foreach ($query1 as $result) {
			$c++;
			$this->assertTrue($result->get('num') >= 100 && $result->get('num') < 200);
		}
		$this->assertEquals($c, $query1->count());
	}
	public function testCount() {
		$query1 = $this->collection->query();
		$this->assertEquals($query1->count(), $this->collection->count());
	}
	public function testCreating() {
		$query1 = $this->collection->query(array('foobar' => mt_rand(0, 232)));
		$query2 = $this->collection->query('cool', mt_rand(1, 100));
		$query3 = $this->collection->query();
	}
	
	public function setUp() {
		$this->connection = Mongodloid_Connection::getInstance();
		$this->db = $this->connection->getDb('test');
		$this->collection_name = 'testcollection' . mt_rand(1000, 9999);
		
		$this->db->getCollection($this->collection_name)->drop();
		
		$this->collection = $this->db->getCollection($this->collection_name);
		for ($i = 0; $i < 1000; $i++) {
			$this->collection->save( new Mongodloid_Entity(array(
				'title' => 'Test item #' . $i,
				'num'   => $i,
				'foobar'=> $i % 232,
				'hi'	=> mt_rand(1, 1000),
				'cool'  => array(mt_rand(1, 100), mt_rand(1, 100), mt_rand(1, 100), mt_rand(1, 100)),
				'gm'	=> array( $i, $i+1, $i+2, $i+3, $i+4, $i+5, $i+6 ),
				'pf'	=> ($i == 7 || $i == 4) ? array(2, 3) : array(3, 4, 5),
				'tr'	=> ($i == 7 || $i == 4) ? array( 'a' => 'b' ) : array( )
			)) );
		}
	}
	
}