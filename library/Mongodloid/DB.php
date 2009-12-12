<?php

require_once 'Collection.php';

class Mongodloid_DB {
	private $_db;
	private $_connection;
	
	protected $_unknownFieldsAllowed = null;
	
	private $_collections = array();
	
	public function areUnknownFieldsAllowed() {
		return ($this->_unknownFieldsAllowed === null) ? $this->_connection->areUnknownFieldsAllowed() :
															$this->_unknownFieldsAllowed;
	}
	
	public function setUnknownFieldsAllowed($flag) {
		$this->_unknownFieldsAllowed = (bool)$flag;
		return $this;
	}
	
	public function getCollection($name, $className = 'Mongodloid_Collection') {
		if (!$this->_collections[$name] || !$this->_collections[$name] instanceOf $className)
			$this->_collections[$name] = new $className($this->_db->selectCollection($name), $this);

		return $this->_collections[$name];
	}
	
	public function getName() {
		return (string)$this->_db;
	}
	
	public function __construct(MongoDb $db, Mongodloid_Connection $connection) {
		$this->_db = $db;
		$this->_connection = $connection;
	}
	
	public function drop() {
		return $this->_db->drop();
	}
}