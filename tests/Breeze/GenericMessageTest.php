<?php


use Breeze\Breeze;
use Breeze\BreezeReader;
use Breeze\BreezeWriter;
use Breeze\Buffer;
use Breeze\FieldDesc;
use Breeze\GenericMessage;
use Breeze\NonSeeker;
use Breeze\Schema;
use Breeze\SchemaSeeker;
use Breeze\Types\TypeMessage;
use Breeze\Types\TypeString;

class GenericMessageTest extends PHPUnit\Framework\TestCase
{
    public function testGetAndSet()
    {
        $gm = $this->getGM();
        // set name index
        $nameIndex = ['ext' => 6, 'ext2' => 8];
        $gm->setNameIndex($nameIndex);

        //__set
        $gm->ext = 'JIO3';
        $gm->ext2 = [45 => 'djkioe', 78 => 'ksoe'];
        $this->assertEquals($gm->getField(6), $gm->ext);
        $this->assertEquals($gm->getField(8), $gm->ext2);

        // put name index
        $gm = $this->getGM();
        $gm->putNameIndex('name', 2);
        $gm->putNameIndex('type', 1);
        $gm->putNameIndex('info', 22);
        $gm->putNameIndex('other', 5);

        // __get
        $this->assertEquals($gm->getField(2), $gm->name);
        $this->assertEquals($gm->getField(1), $gm->type);
        $this->assertEquals($gm->getField(22), $gm->info);
        $this->assertEquals($gm->getField(5), $gm->other);

        // use schema
        Breeze::setSchemaSeeker(new TestSeeker());
        $gm = $this->getGM();
        $gm->name = 'ray';
        $gm->type = 'xxx';
        $gm->age = 100;
        $this->assertEquals($gm->getField(1), $gm->name);
        $this->assertEquals($gm->getField(2), $gm->type);
        $this->assertEquals($gm->getField(3), $gm->age);
        Breeze::setSchemaSeeker(new NonSeeker());
    }

    public function testWriteTo()
    {
        $gm = self::getGM();
        $buf = new Buffer();
        BreezeWriter::writeValue($buf, $gm);
        $newBuf = new Buffer($buf->buffer());
        $r = BreezeReader::readValue($newBuf, new TypeMessage(new GenericMessage()));
        $this->assertEquals($gm, $r, 'generic message');
    }

    public function testPutField()
    {
        $array = [
            1 => 'ek()',
            4 => 'ik)(4',
            7 => ''
        ];
        $gm = new GenericMessage();
        foreach ($array as $k => $v) {
            $gm->putField($k, $v);
        }
        foreach ($array as $k => $v) {
            $this->assertEquals($v, $gm->getField($k), 'GenericMessage field');
        }
    }

    private function getGM()
    {
        $gm = new GenericMessage();
        $gm->putField(1, -234);
        $gm->putField(22, 'jIO((*#');
        $gm->putField(2, 'ray');
        $gm->putField(5, [234 => 'joier', 4 => 'oike']);

        return $gm;
    }
}

class TestSeeker implements SchemaSeeker
{
    public function seekSchema($messagName)
    {
        $schema = new Schema();
        $schema->putField(new FieldDesc(1, 'name', TypeString::instance()))
            ->putField(new FieldDesc(2, 'type', TypeString::instance()))
            ->putField(new FieldDesc(3, 'age', TypeString::instance()));
        return $schema;
    }
}
