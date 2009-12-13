<?
require_once dirname(__FILE__) . '\..\..\library\Mongodloid\Connection.php';

class IDsArrayTest extends PHPUnit_Framework_TestCase {
	public function testAll() {
		$collection = Mongodloid_Connection::getInstance()
								->getDb('test')
								->getCollection('hicol');
		$collection->drop();
		$entity1 = $collection->getEntity(array('hi' => 'a'))->save();
		$entity2 = $collection->getEntity(array('hi' => 'b'))->save();
		$entity3 = $collection->getEntity(array('hi' => 'c'))->save();
		$entity4 = $collection->getEntity(array('hi' => 'd'))->save();		
		
		$collection2 = Mongodloid_Connection::getInstance()
								->getDb('test')
								->getCollection('hicol2');
		$collection2->drop();
		$collection2->registerField('ids', 'ids_array', array(
			'collection' => $collection
		));
		$entity = $collection2->getEntity(array(
			'ids' => array(
				$entity1, $entity2, $entity3
			)
		));
		$entity->save();
				
		$this->assertEquals(3, count($entity->get('ids')));
		$count = 0;
		foreach ($entity->get('ids') as $ent) {
			$count++;
			$this->assertEquals(1, strlen($ent->get('hi')));
		}
		$this->assertEquals(3, $count);
		
		
		
		$id = $entity->getId();
		$entityNew = $collection2->getEntity($id);
		
		$this->assertEquals(3, count($entityNew->get('ids')));
		$count = 0;
		foreach ($entityNew->get('ids') as $ent) {
			$count++;
			$this->assertEquals(1, strlen($ent->get('hi')));
		}
		$this->assertEquals(3, $count);
	}
}