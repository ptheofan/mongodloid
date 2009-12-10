<?php

class Mongodloid_Collection {
	private $_collection;
	private $_db;
	
	public function __construct(MongoCollection $collection, Mongodloid_DB $db) {
		$this->_collection = $collection;
		$this->_db = $db;
	}
}