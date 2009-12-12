<?php
class Mongodloid_Cursor implements Iterator {
	private $_cursor;
	
	protected $_collection;
	protected $_entityClass;
	
	public function __construct(MongoCursor $cursor, $collection) {
		$this->_cursor = $cursor;
		$this->_collection = $collection;
		$this->_entityClass = $this->_collection->getEntityClass();
	}
	
	public function count() {
		return $this->_cursor->count();
	}
	
	public function current() {
		return new $this->_entityClass( $this->_cursor->current(), $this->_collection );
	}
 	public function key() {
 		return $this->_cursor->key();
 	}
 	public function next() {
 		return $this->_cursor->next();
 	}
 	public function rewind() {
 		return $this->_cursor->rewind();
 	}
	public function valid() {
		return $this->_cursor->valid();
	}
}