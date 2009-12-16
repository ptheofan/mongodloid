<?
class Mongodloid_Query_PaginatorAdapter
	implements Zend_Paginator_Adapter_Interface {
	private $_query;
	public function __construct(Mongodloid_Query $query) {
		$this->_query = $query;
	}
	public function count() {
		return $this->_query->count();
	}
	public function getItems($offset, $itemCountPerPage) {
		$cursor = $this->_query
					->limit($offset, $itemCountPerPage)
					->cursor();
		return $cursor;
	}
}
