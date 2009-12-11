<?php
require_once '..\library\Mongodloid\Connection.php';

class QueryTest extends PHPUnit_Framework_TestCase 
{
	public function testCreating() {
		
	}
	public function setUp() {
		$this->connection = Mongodloid_Connection::getInstance();
		$this->db = $this->connection->getDb('test');
	}
}