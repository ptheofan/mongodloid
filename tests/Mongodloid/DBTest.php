<?php
require_once '..\library\Mongodloid\Connection.php';

class DBTest extends PHPUnit_Framework_TestCase 
{
	public function testGetCollection() {
		$connection = Mongodloid_Connection::getInstance();
		$db = $connection->getDB('test');
		$collection1 = $db->getCollection('testcollection');
		$collection2 = $db->getCollection('testcollection');
		$this->assertTrue($collection1 === $collection2);
		$this->assertThat($collection1, $this->isInstanceOf('Mongodloid_Collection'));
	}
	
	public function testGetDB() {
		$connection = Mongodloid_Connection::getInstance();
		$db1 = $connection->getDB('test');
		$db2 = $connection->getDB('test');
		$this->assertTrue($db1 === $db2);
		$this->assertThat($db1, $this->isInstanceOf('Mongodloid_DB'));
	}
}