<?php
require_once '..\library\Mongodloid\Connection.php';

class EntityTest extends PHPUnit_Framework_TestCase 
{
	public function testSaveLoadRemove() {
		$collection = Mongodloid_Connection::getInstance()
						->getDb('test')
						->getCollection('testcollection' . mt_rand(123, 456));
		$data = array(
			'title' => 'hi',
			'obj'  => array(
				'omg' => 'value',
				'arr' => array(
					array( 'er' => 'lol' ),
					array( 'er' => 'oops' ),
					array( 'omg' => 'trap' ),
					12345
				)
			)
		);
						
		$entity = new Mongodloid_Entity($data, $collection);
		$entity->save();
		$this->assertThat($entity->get('_id'), $this->isInstanceOf('Mongodloid_ID'));
		$this->assertThat($entity->getId(), $this->isInstanceOf('Mongodloid_ID'));
		$this->assertEquals($entity->get('_id'), $entity->getId());
		
		$entity2 = new Mongodloid_Entity($data);
		$entity2->save($collection);
		$this->assertThat($entity2->getId(), $this->isInstanceOf('Mongodloid_ID'));
		$this->assertNotEquals($entity->getId(), $entity2->getId());
		
		$entity3 = new Mongodloid_Entity($data);
		$entity3->collection($collection);
		$entity3->save();
		$this->assertThat($entity3->getId(), $this->isInstanceOf('Mongodloid_ID'));
		$this->assertNotEquals($entity->getId(), $entity3->getId());
		
		$entity4 = new Mongodloid_Entity($entity3->getId(), $collection);
		$_data = $data;
		$_data['_id'] = $entity4->getId()->getMongoId();
		$this->assertEquals($entity4->getRawData(), $_data);
		
		$entity4->remove();
		
		$entity5 = new Mongodloid_Entity($entity3->getId(), $collection);
		$this->assertEquals($entity5->getRawData(), array());
	}
	
	
	/**
	 * @expectedException Mongodloid_Exception
	 */
	public function testFailSave() {
		$data = array(
			'title' => 'hi',
			'obj'  => array(
				'omg' => 'value',
				'arr' => array(
					array( 'er' => 'lol' ),
					array( 'er' => 'oops' ),
					array( 'omg' => 'trap' ),
					12345
				)
			)
		);
		$entity = new Mongodloid_Entity($data);
		$entity->save();
	}
	public function testGetSet() {
		$data = array(
			'title' => 'hi',
			'obj'  => array(
				'omg' => 'value',
				'arr' => array(
					array( 'er' => 'lol' ),
					array( 'er' => 'oops' ),
					array( 'omg' => 'trap' ),
					12345
				)
			)
		);
		$entity = new Mongodloid_Entity($data);
		$this->assertEquals($entity->get('title'), 'hi');
		$this->assertEquals($entity->get('obj'), $data['obj']);
		$this->assertEquals($entity->get('obj.omg'), 'value');
		$this->assertEquals($entity->get('obj.arr[3]'), 12345);
		$this->assertEquals($entity->get('obj.arr[0]'), array('er' => 'lol'));
		$this->assertEquals($entity->get('obj.arr'), $data['obj']['arr']);
		$this->assertEquals($entity->get('obj.arr[1].er'), 'oops');
		
		$entity->set('obj.arr[1].er', 'zomg');
		$this->assertEquals($entity->get('obj.arr[1].er'), 'zomg');
		
		$entity->set('ne.wob.ject.and[1].arr', 'hello');
		$this->assertEquals($entity->get('ne.wob.ject.and[1].arr'), 'hello');
		$this->assertEquals($entity->get('ne.wob.ject.and[1]'), array('arr' => 'hello'));
		$this->assertEquals($entity->get('ne.wob.ject.and'), array(1 => array('arr' => 'hello')));
	}
	public function testCreating() {
		$collection = Mongodloid_Connection::getInstance()
						->getDb('test')
						->getCollection('testcollection' . mt_rand(123, 456));
		
		$entity1 = new Mongodloid_Entity();
		$this->assertEquals($entity1->getRawData(), array());
		$this->assertNull($entity1->collection());
		
		$entity2 = new Mongodloid_Entity($collection);
		$this->assertEquals($entity2->getRawData(), array());
		$this->assertEquals($entity2->collection(), $collection);
		
		$data = array(
			'title' => 'hi',
			'obj'  => array(
				'omg' => 'value',
				'arr' => array(
					array( 'er' => 'lol' ),
					array( 'er' => 'oops' ),
					array( 'omg' => 'trap' ),
					12345
				)
			)
		);
		$entity3 = new Mongodloid_Entity($data);
		$this->assertEquals($entity3->getRawData(), $data);
		$this->assertNull($entity3->collection());
		
		$entity4 = new Mongodloid_Entity($data, $collection);
		$this->assertEquals($entity4->getRawData(), $data);
		$this->assertEquals($entity4->collection(), $collection);
	}
}