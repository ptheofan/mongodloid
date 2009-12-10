<?php
require_once '..\library\Mongodloid\Connection.php';

class ConnectionTest extends PHPUnit_Framework_TestCase 
{
	public function testGetDB() {
		$connection = Mongodloid_Connection::getInstance();
		$db1 = $connection->getDB('test');
		$db2 = $connection->getDB('test');
		$this->assertTrue($db1 === $db2);
		$this->assertThat($db1, $this->isInstanceOf('Mongodloid_DB'));
	}
	
	/**
	 * @expectedException MongoConnectionException
	 */
	public function testNotValidConnection() {
		$connection = Mongodloid_Connection::getInstance('notlocalhost', '21211');
		$connection->forceConnect();
	}
	
	/**
	 * To avoid already connected errors, we want to run the test in separate process
	 *
	 * @runInSeparateProcess
	 */
	public function testConnection() {
		$connection = Mongodloid_Connection::getInstance();
		$this->assertFalse($connection->isConnected());
		$connection->forceConnect();
		$this->assertTrue($connection->isConnected());
	}
	/**
	 * To avoid already connected errors, we want to run the test in separate process
	 *
	 * @runInSeparateProcess
	 */
	public function testCreation()
	{
		$connection = Mongodloid_Connection::getInstance();
		
		// lazy connection
		$this->assertFalse($connection->isConnected());
		
		// default: No
		$this->assertFalse($connection->isPersistent());
		
		$connection1 = Mongodloid_Connection::getInstance();
		
		$this->assertTrue($connection === $connection1);
		
		// server name
		$connection3 = Mongodloid_Connection::getInstance('localhost', '12345');
		$connection4 = Mongodloid_Connection::getInstance('localhost:12345');
		$this->assertTrue($connection3 === $connection4);
		
		$connection5 = Mongodloid_Connection::getInstance('localhost:12346', true);
		$connection6 = Mongodloid_Connection::getInstance('localhost', '12347', true);
		$this->assertTrue($connection5->isPersistent());
		$this->assertTrue($connection6->isPersistent());
	}
}
