<?php
class Mongodloid_Entity {
	private $_values;
	private $_collection;
	
	private $_lazyUpdate = 0;
	private $_lazyUpdates = array();
	
	// initial settings for overloading,
	// are added after settings in collection class
	static public $_fields = array();	 
	static public $_unknownFieldsAllowed = null;
	static public $_collectionName = null;
	
	static public $_settingsInitialized = false;
	
	const POPFIRST = 1;
	
	private $_atomics = array(
		'inc',
//		'set',
		'push',
		'pushAll',
		'pop',
		'shift',
		'pull',
		'pullAll'
	);
	
	protected function init() {	}
	public static function initSettings() {	}
	
	public function same(Mongodloid_Entity $obj) {
		return $this->getId() &&
				((string)$this->getId() == (string)$obj->getId());
	}
	
	public function equals(Mongodloid_Entity $obj) {
		$data1 = $this->getRawData();
		$data2 = $obj->getRawData();
		unset($data1['_id'], $data2['_id']);
		
		return $data1 == $data2;
	}
	
	public function inArray($key, $value) {
		if ($value instanceOf Mongodloid_Entity) {
			// TODO: Add DBRef checking
			return $this->inArray($key, $value->getId())
					|| $this->inArray($key, (string)$value->getId());
		}
		
		return in_array($value, $this->get($key));
	}

	public function startUpdate() {
		++$this->_lazyUpdate;
		
		return $this;
	}
	
	public function endUpdate() {
		if (--$this->_lazyUpdate <= 0) {
			$this->update($this->_lazyUpdates);
		}
		
		return $this;
	}

	public function __call($name, $params) {
		if (in_array($name, $this->_atomics)) {			
			$value = $this->get($params[0], true);
			
			switch($name) {
				case 'inc':
					if ($params[1] === null)
						$params[1] = 1;
					
					if ($this->collection())
						$params[1] = $this->collection()->translateField(
																$params[0],
																$params[1]);
						
					$value += $params[1];
					break;
				case 'push':
					if ($this->collection())
						$params[1] = $this->collection()->
										translateArrayElementField(
																$params[0],
																$params[1]
															);
					if (!is_array($value))
						$value = array();
					$value[] = $params[1];
					
					break;
				case 'pushAll':
					if (!is_array($value))
						$value = array();
					
					if ($this->collection())
						$params[1] = $this->collection()->translateField(
																$params[0],
																$params[1]);
						
					$value += $params[1];
					break;
				case 'shift':
					$name = 'pop';
					$params[1] = self::POPFIRST;
				case 'pop':
					$params[1] = ($params[1] == self::POPFIRST) ? -1 : 1;
					if ($params[1] == -1) {
						array_shift($value);
					} else {
						array_pop($value);
					}
					break;
				case 'pull':
					if ($this->collection())
						$params[1] = $this->collection()->
										translateArrayElementField(
																$params[0],
																$params[1]
															);
					$_value = array();
					foreach($value as $val) {
						if ($val !== $params[1]) {
							$_value[] = $val;
						}
					}
					$value = $_value;
					
					break;
				case 'pullAll':
					$params[1] = $this->collection()->translateField(
																$params[0],
																$params[1]);
					$_value = array();
					foreach($value as $val) {
						if (!in_array($val, $params[1])) {
							$_value[] = $val;
						}
					}
					
					$value = $_value;
					break;
			}
			
			$value = $this->set($params[0], $value, true);
			
			if ($this->getId()) {
				$this->update(array(
					'$' . $name => array(
						$params[0] => $params[1]
					)
				));
			}
			
			return $this;
		}
		
		throw new Mongodloid_Exception(get_called_class() . '::' . $name . ' does not exists and hasn\'t been trapped in __call()');
	}
	
	public function update($fields) {
		if ($this->_lazyUpdate > 0) {
			$this->_lazyUpdates += $fields;
		} else {
			if (!$this->collection())
				throw new Mongodloid_Exception('You need to specify the collection');
			
			return $this->_collection->update(array(
				'_id' => $this->getId()->getMongoID()
			), $fields);

		}
	}
	
	public function set($key, $value, $dontSend = false) {
		$free_key = preg_replace('@\\[\d+\\]@', '', $key);
		$key = preg_replace('@\\[([^\\]]+)\\]@', '.$1', $key);
		$real_key = $key;
		$result = &$this->_values;
		
		while ($key) {
			list($current, $key) = explode('.', $key, 2);
			$result = &$result[$current];
		};

		if ($this->collection()) {
			$result = $this->_collection->translateField($free_key, $value);
		} else {
			$result = $value;
		}
		
		if (!$dontSend && $free_key && $free_key != '_id'
				&& $this->collection() && $this->getId())
			$this->update(array('$set' => array($real_key => $result)));
		
		return $this;
	}
	
	public function get($key, $raw = false) {
		if (!$key)
			return $this->_values;
			
		$free_key = preg_replace('@\\[\d+\\]@', '', $key);
		$key = preg_replace('@\\[([^\\]]+)\\]@', '.$1', $key);
		$result = $this->_values;
		
		do {
			list($current, $key) = explode('.', $key, 2);
			$result = $result[$current];
		} while ($key !== null);
		
		if (!$raw && $this->collection()) {
			$result = $this->_collection->detranslateField($free_key, $result);
		}
		
		return $result;
	}
	
	public function getId() {
		return $this->get('_id');
	}
	
	
	public function getRawData() {
		return $this->_values;
	}
	public function setRawData($data) {
		if (!is_array($data))
			throw new Mongodloid_Exception('Data must be an array!');
			
		$data = unserialize(serialize($data));
		
		foreach ($data as $key => $value)
			$this->set($key, $value, false);
		
		return $this;
	}
	public function save($collection = null) {
		if ($collection instanceOf Mongodloid_Collection)
			$this->collection($collection);
		
		if (!$this->collection())
			throw new Mongodloid_Exception('You need to specify the collection');
		
		if (!$this->collection()->save($this))
			throw new Mongodloid_Exception('Something wrong with saving');
		
		return $this;
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
			if (! $collection instanceOf Mongodloid_Collection) {
				if (static::$_collectionName) {
					$this->collection(Mongodloid_Connection::getInstance()
									->getDb()
									->getCollection(static::$_collectionName)
									->setEntityClass(get_called_class()));
				}
				$collection = $this->collection();
			}
				
				
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
		
		if (!$collection) {
			if (static::$_collectionName) {
				$this->collection(Mongodloid_Connection::getInstance()
									->getDb()
									->getCollection(static::$_collectionName)
									->setEntityClass(get_called_class()));
			}
		} else {
			$this->collection($collection);
		}
		
		
		$this->setRawData($values);
		
		$this->init();
	}
}
