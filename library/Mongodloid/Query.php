<?php
require_once 'Cursor.php';

class Mongodloid_Query implements IteratorAggregate {
	private $_collection;
	
	private $_operators = array(
		'=='      	  => 'equals',
		'>'       	  => 'greater',
		'>='      	  => 'greaterEq',
		'<'       	  => 'less',
		'<='      	  => 'lessEq',
		'SIZE'	  	  => 'size',
		'EXISTS'  	  => 'exists',
		'NOT EXISTS'  => 'notExists',
		'NOT IN'  	  => 'notIn',
		'IN'      	  => 'in',
		'ALL'     	  => 'all',
		'!='      	  => 'notEq',
		'%'       	  => 'mod',
		'WHERE'       => 'where',
	);
	
	private $_mongoOperators = array(
		'greater'	=> '$gt',
		'greaterEq'	=> '$gte',
		'less'		=> '$lt',
		'lessEq'	=> '$lte',
		'notEq'		=> '$ne',
		'in'		=> '$in',
		'notIn'		=> '$nin',
		'all'		=> '$all',
		'size'		=> '$size',
		'exists'	=> '$exists',
		'notExists'	=> '$exists',
		'mod'		=> '$mod',
	);
	
	private $_params = array();
	private $_sort = null;
	private $_skip = 0, $_limit = null;
	
	private function _parseQuery($str) {
		$exprs = preg_split('@ AND |&&@i', $str);
		foreach ($exprs as $expr) {
			if (preg_match('@(?<left>.*?)(?<operator>%|==|>=|>|<=|<|!=|NOT EXISTS|EXISTS|SIZE|NOT IN|IN|ALL|WHERE)(?<right>.*)@', $expr, $matches)) {
				$right = trim($matches['right']);
				$func = $this->_operators[$matches['operator']];
				if ($matches['operator'] == '%') {
					$right = array_map(function($v) { return (int)trim($v); }, explode('==', $right));
				} else if ($matches['operator'] == 'EXISTS') {
					$right = true;
				} else if ($matches['operator'] == 'NOT EXISTS') {
					$right = false;
				}
				if (is_numeric($right)) {
					$right = (float)$right;
				}
				$this->$func(trim($matches['left']), $right);
			}
		}
	}
	
	public function __construct(Mongodloid_Collection $collection) {
		$this->_collection = $collection;
	}

	public function where($what, $b = null) {
		if ($b)
			$what = $b;
		return $this->query(array(
					'$where' => $what
				));
	}
	
	public function equals($key, $value) {
		return $this->query($key, $value);
	}
	
	public function __call($name, $param) {
		if ($name == 'exists')
			$param[1] = true;
		else if ($name == 'notExists')
			$param[1] = false;
		else if ($name == 'mod' && $param[2])
			$param[1] = array( $param[1], $param[2] );
		
			
		if ($this->_mongoOperators[$name]) {
			if (is_string($param[1])) {
				$param[1] = array_map(function($v){
					$v = trim($v);
					if (is_numeric($v))
						return (float)$v;
					return $v;
				}, explode(',', trim($param[1], '()')));
			}
			return $this->query(array(
					$param[0] => array(
						$this->_mongoOperators[$name] => $param[1]
					)
				));
		}
		throw new Mongodloid_Exception(__CLASS__ . '::' . $name . ' does not exists and hasn\'t been trapped in call');
	}
	
	public function count() {
		return $this->cursor()->count();
	}
	
	public function skip($count) {
		$this->_skip = $count;
		return $this;
	}
	
	public function limit($count, $c = null) {
		if ($c === null) {
			$this->_limit = $count;
		} else {
			$this->_skip = $count;
			$this->_limit = $c;			
		}
		return $this;
	}
	
	public function sort($what, $how = null) {
		if (!$how)
			$how = 1;
		
		if (!is_array($this->_sort))
			$this->_sort = array();
			
		$this->_sort[$what] = $how;
		
		return $this;
	}
	
	public function cursor() {
		$cursor = $this->_collection->find($this->_params);
		
		if ($this->_sort)
			$cursor->sort($this->_sort);
		
		$cursor->skip($this->_skip);
		if ($this->_limit !== null)
			$cursor->limit($this->_limit);
			
		return new Mongodloid_Cursor( $cursor, $this->_collection );
	}
	
	public function query($key, $value = null) {
		if (is_array($key)) {
			foreach($key as $k=>$v) {
				if ( is_array($v) && is_array($this->_params[$k]) )
					$this->_params[$k] += $v;
				else
					$this->_params[$k] = $v;
			}
		} else if ($value) {
			$this->_params[$key] = $value;
		} else if (is_string($key)) {
			$this->_parseQuery( $key );
		}
		
		return $this;
	}
	
	public function getIterator() {
		return $this->cursor();
	}
}