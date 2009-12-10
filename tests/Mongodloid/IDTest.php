<?php
require_once '..\library\Mongodloid\ID.php';

class IDTest extends PHPUnit_Framework_TestCase 
{
	public function testCreationAndComparing()
	{
		// generating random ID's
		$obj1 = new Mongodloid_ID();
		$obj2 = new Mongodloid_ID();
		$this->assertNotEquals($obj1, $obj2);
		
		// from MongoID
		$id1 = new MongoID();
		$obj1 = new Mongodloid_ID($id1);
		$id2 = new MongoID();
		$obj2 = new Mongodloid_ID($id2);
		$this->assertNotEquals($obj1, $obj2);
		
		$id1 = new MongoID();
		$obj1 = new Mongodloid_ID($id1);
		$obj2 = new Mongodloid_ID($id1);
		$this->assertEquals($obj1, $obj2);
		
		// from string
		$id1 = new MongoID();
		$id2 = new MongoID();
		$obj1 = new Mongodloid_ID((string)$id1);
		$obj2 = new Mongodloid_ID((string)$id2);
		$this->assertNotEquals($obj1, $obj2);
		
		$id1 = new MongoID();
		$id2 = new MongoID();
		$obj1 = new Mongodloid_ID((string)$id1);
		$obj2 = new Mongodloid_ID($id2);
		$this->assertNotEquals($obj1, $obj2);
		
		$id1 = new MongoID();
		$obj1 = new Mongodloid_ID((string)$id1);
		$obj2 = new Mongodloid_ID($id1);
		$this->assertEquals($obj1, $obj2);
	}
}
