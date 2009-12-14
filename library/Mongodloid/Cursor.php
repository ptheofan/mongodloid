<?php
class Mongodloid_Cursor
	implements Iterator, Countable, ArrayAccess, SeekableIterator {
	private $_cursor;  // Caching iterator
	private $__cursor; // Cursor itself
	
	private $_array = array();
	
	protected $_collection;
	protected $_entityClass;
	
	private function cachedCursor() {
		if (!$this->_cursor)
			$this->_cursor = new CachingIterator($this->__cursor);
		
		return $this->_cursor;
	}
	
	public function seek($position) {
		try {
			$this->__cursor->skip($position);
			$this->_cursor = null;
		} catch(MongoCursorException $e) {
			$this->rewind();
			while ($position-- > 0)
				$this->next();
		}
	}
	
	public function offsetExists($offset) {
		return $this->cachedCursor()->offsetExists($offset);
	}
	public function offsetGet($offset) {
		if (count($this->_array) <= $offset) {
			$p_count = 1 + floor($offset - count($this->_array)) / 4;
			$it = new LimitIterator($this, count($this->_array), $p_count * 4);
			$this->_array = array_merge($this->_array,
										iterator_to_array($it, false));
		}
		
		return $this->_array[$offset];
	}
	public function offsetSet($offset, $value) {
		throw new Mongodloid_Exception('Mongodloid_Cursor is read-only.');
	}
	public function offsetUnset($offset) {
		throw new Mongodloid_Exception('Mongodloid_Cursor is read-only.');
	}
	
	public function __construct(MongoCursor $cursor, $collection) {
		$this->__cursor = $cursor;
		$this->_collection = $collection;
		$this->_entityClass = $this->_collection->getEntityClass();
	}
	
	public function count() {
		return $this->__cursor->count();
	}
	
	public function current() {
		return new $this->_entityClass( $this->cachedCursor()->current(),
										$this->_collection );
	}
 	public function key() {
 		return $this->cachedCursor()->key();
 	}
 	public function next() {
 		$this->cachedCursor()->next();
 	}
 	public function rewind() {
 		$this->cachedCursor()->rewind();
 	}
	public function valid() {
		return $this->cachedCursor()->valid();
	}
}