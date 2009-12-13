<?
class Mongodloid_IDsArray implements Countable, IteratorAggregate {
	private $_ids;
	private $_collection;
	private $_entityClass;
	
	public function __construct($ids, Mongodloid_Collection $collection) {
		$this->_ids = $ids;
		$this->_collection = $collection;
	}
	
	public function count() {
		return count($this->_ids);
	}
	
	public function query() {
		$query = $this->_collection->query(array(
			'_id' => array(
				'$in' => $this->_ids
				)
		));
		
		if (func_num_args()) {
			$query = call_user_func_array(array($query, 'query'), func_get_args());
		}
		return $query;
	}
	
	public function getIterator() {
		return $this->query();
	}

}
