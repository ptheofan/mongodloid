<?php
require_once '..\library\Mongodloid\Connection.php';

class CollectionTest extends PHPUnit_Framework_TestCase 
{	
    public function testIndexes() {
        $id = mt_rand(1000, 9999);
        $collection = $this->db->getCollection($this->collection_name);
        
        $collection->save(new Mongodloid_Entity(
                array(
                    'field4' => 'hi',
                    'field5' => 'hi',
                    'field6' => 'hi',
                    'field7' => 'hi'
                )
            ));
        $collection->save(new Mongodloid_Entity(
                array(
                    'field3' => 'zomh',
                    'field4' => 'hi',
                    'field5' => 'hi',
                    'field6' => 'hi',
                    'field7' => 'hi'
                )
            ));
        
        $collection->ensureIndex('field');
        $this->assertEquals($collection->getIndexedFields(), array('_id', 'field'));
        $collection->ensureIndex(array('field1' => 1, 'field2' => -1));
        
        $found = false;
        foreach($collection->getIndexes() as $index) {
            if ($index->get('key.field1')) {
                $this->assertEquals($index->get('key.field1'), 1);
                $this->assertEquals($index->get('key.field2'), -1);
                $found = true;
            }
        }
        $this->assertTrue($found);
        
        $collection->ensureIndex('field3', Mongodloid_Collection::UNIQUE);
        $found = false;
        foreach($collection->getIndexes() as $index) {
            if ($index->get('key.field3')) {
                $this->assertTrue($index->get('unique'));
                $found = true;
            }
        }
        $this->assertTrue($found);
        
        $collection->ensureIndex('field4', Mongodloid_Collection::UNIQUE); // has dups
        $found = false;
        foreach($collection->getIndexes() as $index) {
            if ($index->get('key.field4')) {
                $this->assertTrue($index->get('unique'));
                $found = true;
            }
        }
        $this->assertFalse($found);
        
        $this->assertEquals($collection->count(), 2);
        if (Mongo::VERSION != '1.0.1') {
            $collection->ensureIndex('field4', Mongodloid_Collection::DROP_DUPLICATES);  // unique AND dropDups
            $this->assertEquals($collection->count(), 1);
        }
        $collection->save(new Mongodloid_Entity(
                array(
                    'field4' => 'hi',
                    'field5' => 'hi',
                    'field6' => 'hi',
                    'field7' => 'hi'
                )
            ));
        $collection->ensureUniqueIndex('field9');
        if (Mongo::VERSION != '1.0.1') {
            $collection->ensureUniqueIndex('field6', true); // dropDups
            $this->assertEquals($collection->count(), 1);
        }
        $collection->dropIndex('field');
        $this->assertNotContains('field', $collection->getIndexedFields());
        
        //$collection->reIndex(); // not sure how to test it
        
        $collection->dropIndexes(); // and it
        
        $collection->drop();
    }
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
