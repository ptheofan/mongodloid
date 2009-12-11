<?php

require_once 'Entity.php';

class Mongodloid_Collection {
	private $_collection;
	private $_db;
	
	public function __construct(MongoCollection $collection, Mongodloid_DB $db) {
		$this->_collection = $collection;
		$this->_db = $db;
	}
	
	public function save(Mongodloid_Entity $entity) {
		$data = $entity->getRawData();
		
		$result = $this->_collection->save($entity->getRawData());
		if (!$result)
			return false;
			
		$entity->setRawData($data);
		return true;
	}
	
	public function findOne($id, $want_array = false) {
		$values = $this->_collection->findOne(array('_id' => $id->getMongoId()));
		if ($want_array)
			return $values;
		
		return new Mongodloid_Entity($values, $this);
	}
	
	public function drop() {
		return $this->_collection->drop();
	}
	
	public function count() {
		return $this->_collection->count();
	}
	
	public function clear() {
		return $this->remove(array());
	}
	
	public function remove($query) {
		if ($query instanceOf Mongodloid_Entity)
			$query = $query->getId();
			
		if ($query instanceOf Mongodloid_ID)
			$query = array('_id' => $query->getMongoId());
		
		return $this->_collection->remove($query);
	}
}