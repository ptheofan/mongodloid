<?php
require_once '..\library\Mongodloid\Connection.php';

class EntityTest extends PHPUnit_Framework_TestCase 
{
	public function testHelpers() {
		$collection = Mongodloid_Connection::getInstance()
						->getDb('test')
						->getCollection('testcollection' . mt_rand(123, 456));
						
		$entity1 = new Mongodloid_Entity(array(
			'hi' => array(1, 2, 3)
		), $collection);
		$entity1->save();
		$entity2 = new Mongodloid_Entity(array(
			'hi' => array(1, 2, 3)
		), $collection);
		$entity2->save();
		$entity3 = new Mongodloid_Entity($entity1->getId(), $collection);
		$entity4 = new Mongodloid_Entity(array(
			'hi' => array(1, 2, 3, 4)
		), $collection);
		$entity4->save();
		
		$this->assertTrue($entity1->same($entity3));
		$this->assertFalse($entity1->same($entity2));
		$this->assertFalse($entity1->same($entity4));
		$this->assertTrue($entity1->equals($entity3));
		$this->assertTrue($entity1->equals($entity2));
		$this->assertFalse($entity1->equals($entity4));

		
		
		$entity1->set('ids', array($entity1->getId(), $entity2->getId()));
		$this->assertTrue($entity1->inArray('hi', 3));
		$this->assertFalse($entity1->inArray('hi', 4));
		$this->assertTrue($entity1->inArray('ids', $entity2));
		$this->assertFalse($entity1->inArray('ids', $entity4));
		
		$collection->drop();
	}
	public function testAtomics() {
		$collection = Mongodloid_Connection::getInstance()
						->getDb('test')
						->getCollection('testcollection' . mt_rand(123, 456));
		$data = array(
			'a' => 18,
			'b' => 'hello',
			'c' => array(),
			//'d' => array(),
			'e' => array(1, 2, 3),
			'f' => array(1, 2, 3),
			'g' => array(1, 2, 3),
			'h' => array(1, 2, 3)
		);
						
		$entity = new Mongodloid_Entity($data, $collection);
		$entity->save();
		$id = $entity->getId();
		
		$entity->inc('a')->inc('a', 3); // chaining!
		$this->assertEquals($entity->get('a'), 22);
		$tEntity = new Mongodloid_Entity($id, $collection);
		$this->assertEquals($tEntity->get('a'), 22);
		
		$entity->set('b', 'hi');
		$this->assertEquals($entity->get('b'), 'hi');
		$tEntity = new Mongodloid_Entity($id, $collection);
		$this->assertEquals($tEntity->get('b'), 'hi');
		
		$entity->push('c', 1221);
		$this->assertEquals($entity->get('c'), array(1221));
		$tEntity = new Mongodloid_Entity($id, $collection);
		$this->assertEquals($tEntity->get('c'), array(1221));
		
		$entity->pushAll('d', array(8, 5, 9, 6));
		$this->assertEquals($entity->get('d'), array(8, 5, 9, 6));
		$tEntity = new Mongodloid_Entity($id, $collection);
		$this->assertEquals($tEntity->get('d'), array(8, 5, 9, 6));
		
		$entity->pop('e');
		$this->assertEquals($entity->get('e'), array(1, 2));
		$tEntity = new Mongodloid_Entity($id, $collection);
		$this->assertEquals($tEntity->get('e'), array(1, 2));
		
		$entity->pop('f', Mongodloid_Entity::POPFIRST);
		$this->assertEquals($entity->get('f'), array(2, 3));
		$tEntity = new Mongodloid_Entity($id, $collection);
		$this->assertEquals($tEntity->get('f'), array(2, 3));
		
		$entity->shift('f');
		$this->assertEquals($entity->get('f'), array(3));
		$tEntity = new Mongodloid_Entity($id, $collection);
		$this->assertEquals($tEntity->get('f'), array(3));
		
		$entity->pull('g', 2);
		$this->assertEquals($entity->get('g'), array(1, 3));
		$tEntity = new Mongodloid_Entity($id, $collection);
		$this->assertEquals($tEntity->get('g'), array(1, 3));
		
		$entity->pullAll('h', array(1, 3));
		$this->assertEquals($entity->get('h'), array(2));
		$tEntity = new Mongodloid_Entity($id, $collection);
		$this->assertEquals($tEntity->get('h'), array(2));
		
		$collection->drop();
	}
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