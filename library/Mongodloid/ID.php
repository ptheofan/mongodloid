<?php

class Mongodloid_ID {
	private $_mongoID;
	private $_stringID;
	
	public function setMongoID(MongoID $id) {
		$this->_mongoID = $id;
		$this->_stringID = (string)$this->_mongoID;
	}
	
	public function __construct($base = null) {
		if ($base instanceOf MongoID) {
			$this->setMongoID($base);
		} else {
			$this->setMongoID(new MongoID($base));
		}
	}
}
