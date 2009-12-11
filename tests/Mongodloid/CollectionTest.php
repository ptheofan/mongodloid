<?php
require_once '..\library\Mongodloid\Connection.php';

class CollectionTest extends PHPUnit_Framework_TestCase 
{	
	public function testGetCollection() {
		$collection = $this->db->getCollection('testcollection' . mt_rand(1000, 9999));
	}
	public function testDropCollection() {
		$id = mt_rand(1000, 9999);
		$collection = $this->db->getCollection($this->collection_name);
		$collection->save(
			new Mongodloid_Entity(
				array(
					'title' => 'hi'
				)
			)
		);
		$collection->drop();
		
		$collection = $this->db->getCollection($this->collection_name);
		$this->assertEquals($collection->count(), 0);
	}
	
	public function testSaveAndCount() {
		$collection = $this->db->getCollection($this->collection_name);
		$collection->clear();
		
		$count = mt_rand(100, 1000);
		for ($i = 0; $i < $count; $i++) {
			$collection->save(
				new Mongodloid_Entity(
					array(
						'title' => 'hi'
					)
				)
			);
		}
		$this->assertEquals($collection->count(), $count);
		$collection->drop();
	}
	
	public function setUp() {
		$this->connection = Mongodloid_Connection::getInstance();
		$this->db = $this->connection->getDb('test');
		$this->collection_name = 'testcollection' . mt_rand(1000, 9999);
	}
}