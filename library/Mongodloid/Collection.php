<?php
require_once 'Entity.php';
require_once 'Query.php';

class Mongodloid_Collection {
	private $_collection;
	private $_db;
	private $__fields = array();
	
	protected $_entityClass = 'Mongodloid_Entity';
	protected $_fields = array();	 // initial settings for overloading
	protected $_unknownFieldsAllowed = true;
	
	const UNIQUE = 'unique';
	const DROP_DUPLICATES = 'dropdups';
	
	public function __construct(MongoCollection $collection, Mongodloid_DB $db) {
		$this->_collection = $collection;
		$this->_db = $db;
		
		$this->init();
		$this->registerFields($this->_fields);
	}
	
	protected function init() { }

	public function areUnknownFieldsAllowed() {
		return $this->_unknownFieldsAllowed;
	}
	
	public function setUnknownFieldsAllowed($flag) {
		$this->_unknownFieldsAllowed = (bool)$flag;
		return $this;
	}

	public function translateField($key, $value) {
		$key = preg_replace('@(^|.)(\d+)(.|$)@', '.', $key);
		
		if (is_array($value)) {
			
			$self =& $this;
			array_walk(&$value, function(&$_value, $_key) use (&$self, $key) {
				$_value = $self->translateField( trim($key . '.' . $_key, '.'), $_value );
			});
			
		} else {
			$key = (string)$key;
		
			$info = $this->getFieldInfo($key);
			
			if (!$info) {
				if (!$this->areUnknownFieldsAllowed())
					throw new Mongodloid_Exception('Field ' . $key . ' is not allowed.');
				
				return $value;
			}
			
			if ($info['type'])
				settype($value, $info['type']);
		}
		return $value;
	}

	public function getFieldInfo($name) {
		return $this->__fields[$name];
	}

	public function registerFields($fields) {
		foreach($fields as $name => $params) {
			$this->registerField($name, $params);
		}
	}

	public function registerField($name, $type = null, $params = array()) {
		if (is_array($type)) {
			$params = $type;
			$type = null;
		}
		
		if ($type) {
			$params['type'] = $type;
		}
		
		$_params = array();
		foreach($params as $id => $value) {
			if (is_string($id) && $value)
				$_params[$id] = $value;
			else if (is_string($value))
				$_params[$value] = true;
		}
		$params = $_params;
		
		$this->__fields[$name] = $params;
		
		if ($params['unique']) {
			$this->ensureUniqueIndex($name);
		} else if ($params['index']) {
			$this->ensureIndex($name);
		}
	}

	public function getEntity() {
		$args = func_get_args();
		$args[1] = $this;
		$reflectionClass = new ReflectionClass( $this->_entityClass ); 
        $entity = $reflectionClass->newInstanceArgs( $args );
        return $entity;
	}

	public function getEntityClass() {
		return $this->_entityClass;
	}
	
	public function setEntityClass($className) {
		$this->_entityClass = $className;
		return $this;
	}
	
	public function update($query, $values) {
		return $this->_collection->update($query, $values);
	}
	
	public function getName() {
		return $this->_collection->getName();
	}

	public function dropIndexes() {
		return $this->_collection->deleteIndexes();
	}
	
	public function dropIndex($field) {
		return $this->_collection->deleteIndex($field);
	}
	
	public function ensureUniqueIndex($fields, $dropDups = false) {
		return $this->ensureIndex($fields, $dropDups ? self::DROP_DUPLICATES : self::UNIQUE);
	}
	
	public function ensureIndex($fields, $params = array()) {
		if (!is_array($fields))
			$fields = array($fields => 1);
			
		$ps = array();
		if ($params == self::UNIQUE || $params == self::DROP_DUPLICATES)
			$ps['unique'] = true;
		if  ($params == self::DROP_DUPLICATES)
			$ps['dropDups'] = true;
		
		// I'm so sorry :(
		if (Mongo::VERSION == '1.0.1')
			$ps = (bool)$ps['unique'];
			
		return $this->_collection->ensureIndex($fields, $ps);
	}
	
	public function getIndexedFields() {
		$indexes = $this->getIndexes();

		$fields = array();
		foreach($indexes as $index) {
			$keys = array_keys($index->get('key'));
			foreach($keys as $key)
				$fields[] = $key;
		}
		
		return $fields;
	}
	
	public function getIndexes() {
		$indexCollection = $this->_db->getCollection('system.indexes');
		return $indexCollection->query('ns', $this->_db->getName() . '.' . $this->getName());
	}
	
	public function query() {
		$query = new Mongodloid_Query($this);
		if (func_num_args()) {
			$query = call_user_func_array(array($query, 'query'), func_get_args());
		}
		return $query;
	}
	
	public function save(Mongodloid_Entity $entity) {
		foreach ($this->__fields as $name => $field)
			if ($field['required'] && !$entity->get($name))
				throw new Mongodloid_Exception('Field ' . $name . ' is required!');
		
		$data = $entity->getRawData();
		
		$result = $this->_collection->save($data);
		if (!$result)
			return false;
			
		$entity->setRawData($data);
		return true;
	}
	
	public function findOne($id, $want_array = false) {
		$values = $this->_collection->findOne(array('_id' => $id->getMongoId()));
		if ($want_array)
			return $values;
		
		return new $this->_entityClass($values, $this);
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
	
	public function find($query) {
		return $this->_collection->find($query);
	}
}