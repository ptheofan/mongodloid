<?php
require_once 'PHPUnit/Framework.php';
 
class MongodloidTest extends PHPUnit_Framework_TestCase
{
    /**   MongoID is == another MongoID without any good reasons
     *    so I need to store a string version of ID in Mongodloid_ID
     *    here I'm testing if MongoID still has this bug
     *    (if it doesn't, it's time to delete MongodloidID::$_stringID)
     */
    public function testMongoIDs()
    {
        $id1 = new MongoID();
        $id2 = new MongoID();
        $this->assertEquals($id1, $id2);
        $this->assertNotEquals($id1->__toString(), $id2->__toString());
        $this->assertNotEquals((string)$id1, (string)$id2);
        
        $id3 = new MongoID((string)$id1);
        $this->assertEquals($id1, $id3);
        $this->assertEquals($id2, $id3);
        $this->assertEquals($id1->__toString(), $id3->__toString());
        $this->assertNotEquals((string)$id3, (string)$id2);
    }
}
?>