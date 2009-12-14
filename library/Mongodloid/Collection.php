<?php
require_once 'Entity.php';
require_once 'Query.php';
require_once 'IDsArray.php';

class Mongodloid_Collection {
	private $_collection;
	private $_db;
	protected $__fields = array();
	
	protected $_entityClass = 'Mongodloid_Entity';
	protected $_fields = array();	 // initial settings for overloading
	protected $_unknownFieldsAllowed = null;
	
	const UNIQUE = 'unique';
	const DROP_DUPLICATES = 'dropdups';
	
	public function __construct(MongoCollection $collection, Mongodloid_DB $db) {
		$this->_collection = $collection;
		$this->_db = $db;
		
		$this->init();
		$this->registerFields($this->_fields);
		$this->setEntityClass($this->_entityClass);
	}
	
	protected function init() { }

	public function areUnknownFieldsAllowed() {
		//var_dump($this->_unknownFieldsAllowed); die;
		return ($this->_unknownFieldsAllowed === null) ?
					$this->_db->areUnknownFieldsAllowed() :
						$this->_unknownFieldsAllowed;
	}
	
	public function setUnknownFieldsAllowed($flag) {
		$this->_unknownFieldsAllowed = (bool)$flag;
		return $this;
	}

	public function detranslateField($key, $value) {
		if ($key == '_id') {
			return new Mongodloid_ID($value);
		}
		
		$key = preg_replace('@(^|\\.)(\d+)(\\.|$)@', '.', $key);
		
		$info = $this->getFieldInfo($key);
		
		if (is_array($value) && $info['type'] != 'array') {
			
			$self =& $this;
			array_walk(&$value, function(&$_value, $_key) use (&$self, $key) {
				$_value = $self->detranslateField(
									trim($key . '.' . $_key, '.'),
									$_value
								);
			});
			
		} else {
			$key = (string)$key;
			
			if (!is_array($info)) {				
				return $value;
			}
			
			if ($info['type']) {
				switch ($info['type']) {
					case 'id':
						if (is_string($info['collection'])) {
							$info['collection'] =
								$this->_db->getCollection(
										$info['collection']
									);
						}
						$value = $info['collection']->getEntity(
												new Mongodloid_ID($value));
						break;
					case 'array':
						if ($info['of'] == 'id') {
							if (is_string($info['collection'])) {
								$info['collection'] =
									$this->_db->getCollection(
											$info['collection']
										);
							}
						
							$value = new Mongodloid_IDsArray($value,
															 $info['collection']
															);
						}

						break;
				}
			}
				
		}
		return $value;
	}

	public function translateArrayElementField($key, $value) {
		$key = preg_replace('@(^|\\.)(\d+)(\\.|$)@', '.', $key);
		$info = $this->getFieldInfo($key);
		
		if (!$info)
			return $value;
		
		if ($info['of']) {
			switch ($info['of']) {
				case 'id':
					if ($value instanceOf Mongodloid_Entity)
						$value = $value->getId()->getMongoId();
					break;
				default:
					settype($value, $info['of']);
			}
		}
		
		return $value;
	}

	public function translateField($key, $value) {
		$key = preg_replace('@(^|\\.)(\d+)(\\.|$)@', '.', $key);
		
		$info = $this->getFieldInfo($key);
		
		if (is_array($value) && $info['type'] != 'array') {
			
			$self =& $this;
			array_walk(&$value, function(&$_value, $_key) use (&$self, $key) {
				$_value = $self->translateField( trim($key . '.' . $_key, '.'),
												 $_value );
			});
			
		} else {
			$key = (string)$key;
			
			if (!is_array($info)) {
				if ($key != '_id' && !$this->areUnknownFieldsAllowed())
					throw new Mongodloid_Exception(
							'Field ' . $key . ' is not allowed.'
						);
				
				return $value;
			}
			
			if ($info['type']) {
				switch ($info['type']) {
					case 'id':
						if ($value instanceOf Mongodloid_Entity)
							$value = $value->getId()->getMongoId();
						break;
					case 'enum':
						if (!in_array($value, $info['values']))
							throw new Mongodloid_Exception(
									'Invalid value for enum ' . $key
								);
						break;
					case 'array':
						$self =& $this;
						$value = array_map(function($entity) use($self, $key) {
							return $self->translateArrayElementField($key,
																	 $entity);
						}, $value);
						if ($info['array_unique']) {
							$value = array_unique($value);
						}
						
						break;
					default:
						settype($value, $info['type']);
				}
			}
				
		}
		return $value;
	}

	public function getFieldInfo($name) {
		return $this->__fields[$name];
	}

	public function registerFields($fields, $prep = '') {
		foreach($fields as $name => $params) {
			$this->registerField(trim($prep . '.' . $name, '.'), $params);
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
			if (is_string($id) && $value) {
				$_id = $id;
				$_value = $value;
			} else if (is_string($value)) {
				$_id = $value;
				$_value = true;
			}
			if (substr($_id, 0, 6) == 'field.') {
				list(, $f_key) = explode('.', $_id);
				$_params['subfields'][$f_key] = $_value;
			}			
			$_params[$_id] = $_value;
		}
		$params = $_params;
		
		$this->__fields[$name] = $params;
		
		if ($params['unique']) {
			$this->ensureUniqueIndex($name);
		} else if ($params['index']) {
			$this->ensureIndex($name);
		}
		
		if ($params['subfields'])
			$this->registerFields($params['subfields'], $name);
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
		
		if (!$className::$_settingsInitialized) {
			$className::initSettings();
		}
		
		$this->registerFields($className::$_fields);
		if ($className::$_unknownFieldsAllowed !== null) {
			$this->setUnknownFieldsAllowed($className::$_unknownFieldsAllowed);
		}

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
			if ($field['required'] && ($entity->get($name) === null))
				throw new Mongodloid_Exception(
						'Field "' . $name . '" is required!'
					);
		
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