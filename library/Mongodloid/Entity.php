<?php
class Mongodloid_Entity {
	private $_values;
	private $_collection;
	
	public function set($key, $value) {
		$key = preg_replace('@\\[([^\\]]+)\\]@', '.$1', $key);
		$result = &$this->_values;
		
		do {
			list($current, $key) = explode('.', $key, 2);
			$result = &$result[$current];
		} while ($key !== null);
		
		$result = $value;
	}
	
	public function get($key) {
		if (!$key)
			return $this->_values;
		if ($key == '_id')
			return $this->getId();
		
		$key = preg_replace('@\\[([^\\]]+)\\]@', '.$1', $key);
		$result = $this->_values;
		
		do {
			list($current, $key) = explode('.', $key, 2);
			$result = $result[$current];
		} while ($key !== null);
		
		return $result;
	}
	
	public function getId() {
		if (!$this->_values['_id'])
			return false;
			
		return new Mongodloid_ID($this->_values['_id']);
	}
	
	
	public function getRawData() {
		return $this->_values;
	}
	public function setRawData($data) {
		if (!is_array($data))
			throw new Mongodloid_Exception('Data must be an array!');
			
		// prevent from making a link
		$this->_values = unserialize(serialize($data));
	}
	public function save($collection = null) {
		if ($collection instanceOf Mongodloid_Collection)
			$this->collection($collection);
		
		if (!$this->collection())
			throw new Mongodloid_Exception('You need to specify the collection');
		
		return $this->collection()->save($this);
	}
	
	public function collection($collection = null) {
		if ($collection instanceOf Mongodloid_Collection)
			$this->_collection = $collection;
		
		return $this->_collection;
	}
	
	public function remove() {
		if (!$this->collection())
			throw new Mongodloid_Exception('You need to specify the collection');
			
		if (!$this->getId())
			throw new Mongodloid_Exception('Object wasn\'t saved!');
		
		return $this->collection()->remove($this);
	}
	
	public function __construct($values = null, $collection = null) {
		if ($values instanceOf Mongodloid_ID) {
			if (! $collection instanceOf Mongodloid_Collection)
				throw new Mongodloid_Exception('You need to specify the collection');
			
			$values = $collection->findOne($values, true);
		}
		
		if ($values instanceOf Mongodloid_Collection) {
			$collection = $values;
			$values = null;
		}
		
		if (!is_array($values))
			$values = array();
		
		$this->setRawData($values);
		$this->collection($collection);
	}
}