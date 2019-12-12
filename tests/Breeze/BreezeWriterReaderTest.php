<?php


use Breeze\Breeze;
use Breeze\BreezeReader;
use Breeze\BreezeWriter;
use Breeze\Buffer;
use Breeze\Test\MyEnum;
use Breeze\Test\TestMsg;
use Breeze\Test\TestStruct;
use Breeze\Test\TestSubMsg;
use Breeze\Types\Type;
use Breeze\Types\TypeBool;
use Breeze\Types\TypeByte;
use Breeze\Types\TypeBytes;
use Breeze\Types\TypeFloat32;
use Breeze\Types\TypeFloat64;
use Breeze\Types\TypeInt16;
use Breeze\Types\TypeInt32;
use Breeze\Types\TypeInt64;
use Breeze\Types\TypeMessage;
use Breeze\Types\TypePackedArray;
use Breeze\Types\TypePackedMap;
use Breeze\Types\TypeString;

class BreezeWriterReaderTest extends PHPUnit\Framework\TestCase
{
    public function testWriteValue()
    {
        $array = [
            new TestStruct(123, TypeInt32::instance()),
            new TestStruct(true, TypeBool::instance()),
            new TestStruct('j)(*U""', TypeString::instance()),
            new TestStruct([123, 45, 657], new TypePackedArray(TypeInt32::instance())),
            new TestStruct(['ser', 'er3', 1234], new TypePackedArray(TypeString::instance())),
            new TestStruct([0 => 'se', 1 => 'er', 2 => 'tr'], new TypePackedArray(TypeString::instance())),
            new TestStruct([123 => 'erw', 45 => 'wer', 657 => 'terd'], new TypePackedMap(TypeInt32::instance(), TypeString::instance())),
            new TestStruct(['ser' => 45, 'er3' => 678], new TypePackedMap(TypeString::instance(), TypeInt32::instance())),
            new TestStruct([1 => 'se', 2 => 'er', 4 => 'tr'], new TypePackedMap(TypeInt32::instance(), TypeString::instance())),
            new TestStruct($this->getTestMsg(), new TypeMessage(new TestMsg(false))),
            new TestStruct(new MyEnum(MyEnum::E2), new TypeMessage(new MyEnum())),
        ];
        //with type
        foreach ($array as $v) {
            $buf = new Buffer();
            BreezeWriter::writeValue($buf, $v->v, $v->t);
            $newBuf = new Buffer($buf->buffer());
            $r = $v->t->read($newBuf, true);
            $this->assertEquals($v->v, $r, 'write value');
        }
        // write without type
        foreach ($array as $v) {
            $buf = new Buffer();
            BreezeWriter::writeValue($buf, $v->v);
            $newBuf = new Buffer($buf->buffer());
            $r = $v->t->read($newBuf, true);
            $this->assertEquals($v->v, $r, 'write value');
        }

        // read without type
        Breeze::registerMessage(new TestMsg());// need register message if read without type
        Breeze::registerMessage(new MyEnum());
        foreach ($array as $v) {
            $buf = new Buffer();
            BreezeWriter::writeValue($buf, $v->v, $v->t);
            $newBuf = new Buffer($buf->buffer());
            $r = BreezeReader::readValue($newBuf);
            $this->assertEquals($v->v, $r, 'write value');
        }
    }

    public function testWriteString()
    {
        $array = [
            12355,
            '',
            'jOPI*()#UJf',
            '879',
            '-78293',
            "\n",
            '\n'
        ];
        foreach ($array as $v) {
            $buf = new Buffer();
            BreezeWriter::writeString($buf, $v);
            $newBuf = new Buffer($buf->buffer());
            $r = TypeString::instance()->read($newBuf);
            $this->assertEquals($v, $r, 'write string');
        }
    }

    public function testWriteInt32()
    {
        $array = [
            12,
            -234,
            5467,
            87897,
            0,
        ];
        foreach ($array as $v) {
            $buf = new Buffer();
            BreezeWriter::writeInt32($buf, $v);
            $newBuf = new Buffer($buf->buffer());
            $r = TypeInt32::instance()->read($newBuf);
            $this->assertEquals($v, $r, 'write int32');
        }
    }

    public function testWriteFloat32()
    {
        $array = [
            12,
            -234,
            54.67,
            878.97,
            0,
        ];
        foreach ($array as $v) {
            $buf = new Buffer();
            BreezeWriter::writeFloat32($buf, $v);
            $newBuf = new Buffer($buf->buffer());
            $r = TypeFloat32::instance()->read($newBuf);
            $this->assertEquals(sprintf("%b", $v), sprintf("%b", $r), 'write float32');
        }
    }

    public function testWriteInt64()
    {
        $array = [
            12,
            -234,
            546739048598,
            -832479094490545,
            0,
        ];
        foreach ($array as $v) {
            $buf = new Buffer();
            BreezeWriter::writeInt64($buf, $v);
            $newBuf = new Buffer($buf->buffer());
            $r = TypeInt64::instance()->read($newBuf);
            $this->assertEquals($v, $r, 'write int64');
        }
    }

    public function testWriteBytes()
    {
        $array = [
            pack('N', 345),
            -234,
            'fwe8945',
            '789',
            0,
        ];
        foreach ($array as $v) {
            $buf = new Buffer();
            BreezeWriter::writeBytes($buf, $v);
            $newBuf = new Buffer($buf->buffer());
            $r = TypeBytes::instance()->read($newBuf);
            $this->assertEquals($v, $r, 'write bytes');
        }
    }

    public function testWriteFloat64()
    {
        $array = [
            12,
            -234,
            54.67,
            878.97,
            237498723549.3983745,
            -38945.84890576460,
            0,
        ];
        foreach ($array as $v) {
            $buf = new Buffer();
            BreezeWriter::writeFloat64($buf, $v);
            $newBuf = new Buffer($buf->buffer());
            $r = TypeFloat64::instance()->read($newBuf);
            $this->assertEquals(sprintf("%b", $v), sprintf("%b", $r), 'write float64');
        }
    }

    public function testWriteBool()
    {
        $array = [
            true,
            false
        ];
        foreach ($array as $v) {
            $buf = new Buffer();
            BreezeWriter::writeBool($buf, $v);
            $newBuf = new Buffer($buf->buffer());
            $r = TypeBool::instance()->read($newBuf);
            $this->assertEquals($v, $r, 'write bool');
        }
    }

    public function testWriteByte()
    {
        $array = [
            '',
            'j',
            '8',
            '%',
            123,
            45,
            "\n",
        ];
        foreach ($array as $v) {
            $buf = new Buffer();
            BreezeWriter::writeByte($buf, $v);
            $newBuf = new Buffer($buf->buffer());
            $r = TypeByte::instance()->read($newBuf);
            $this->assertEquals((int)$v, $r, 'write byte');
        }
    }

    public function testWriteInt16()
    {
        $array = [
            12,
            -234,
            5467,
            26897,
            0,
        ];
        foreach ($array as $v) {
            $buf = new Buffer();
            BreezeWriter::writeInt16($buf, $v);
            $newBuf = new Buffer($buf->buffer());
            $r = TypeInt16::instance()->read($newBuf);
            $this->assertEquals($v, $r, 'write int16');
        }
    }

    public function testWriteArray()
    {
        $array = [
            new TestStruct([123, 45, 657], new TypePackedArray(TypeInt32::instance())),
            new TestStruct(['ser', 'er3'], new TypePackedArray(TypeString::instance())),
            new TestStruct([0 => 'se', 1 => 'er', 2 => 'tr'], new TypePackedArray(TypeString::instance())),
        ];
        foreach ($array as $v) {
            $buf = new Buffer();
            BreezeWriter::writeArray($buf, $v->v, $v->t->getElemType());
            $newBuf = new Buffer($buf->buffer());
            $r = $v->t->read($newBuf);
            $this->assertEquals($v->v, $r, 'write array');
        }
    }

    public function testWriteMap()
    {
        $array = [
            new TestStruct([123 => 'erw', 45 => 'wer', 657 => 'terd'], new TypePackedMap(TypeInt32::instance(), TypeString::instance())),
            new TestStruct(['ser' => 45, 'er3' => 678], new TypePackedMap(TypeString::instance(), TypeInt32::instance())),
            new TestStruct([1 => 'se', 2 => 'er', 4 => 'tr'], new TypePackedMap(TypeInt16::instance(), TypeString::instance())),
        ];
        foreach ($array as $v) {
            $buf = new Buffer();
            BreezeWriter::writeMap($buf, $v->v, $v->t->getElemType()[0], $v->t->getElemType()[1]);
            $newBuf = new Buffer($buf->buffer());
            $r = $v->t->read($newBuf);
            $this->assertEquals($v->v, $r, 'write map');
        }
    }

    public function testWriteMessage()
    {
        $buf = new Buffer();
        $name = 'msg name';
        BreezeWriter::writeMessageType($buf, $name);
        BreezeWriter::writeMessage($buf, function (Buffer $fbuf) {
            BreezeWriter::writeMessageField($fbuf, 1, '234', TypeString::instance());
            BreezeWriter::writeMessageField($fbuf, 2, -234, TypeInt32::instance());
            BreezeWriter::writeMessageField($fbuf, 3, $this->getTestMsg(), new TypeMessage(new TestMsg()));
        });
        $newBuf = new Buffer($buf->buffer());
        $tp = $newBuf->readByte();
        $this->assertEquals(Type::T_MESSAGE, $tp, 'message type');
        $rname = TypeString::instance()->read($newBuf);
        $this->assertEquals($name, $rname, 'message name');
        BreezeReader::readMessage($newBuf, function (Buffer $fbuf, $index) {
            switch ($index) {
                case 1:
                    $this->assertEquals('234', TypeString::instance()->read($fbuf), 'write messge');
                    return;
                case 2:
                    $this->assertEquals(-234, TypeInt32::instance()->read($fbuf), 'write messge');
                    return;
                case 3:
                    $type3 = new TypeMessage(new TestMsg());
                    $this->assertEquals($this->getTestMsg(), $type3->read($fbuf), 'write messge');
                    return;
            }
        });
        $this->assertEquals($newBuf->pos(), $newBuf->len(), 'buffer pos');
    }

    public function testIs_assoc()
    {
        // associate
        $array = [
            [123 => 'erw', 45 => 'wer', 657 => 'terd'],
            ['ser' => 45, 'er3' => 678],
            [1 => 'se', 2 => 'er', 4 => 'tr'],
            []
        ];

        foreach ($array as $v) {
            $b = BreezeWriter::is_assoc($v);
            if (!$b) {
                var_dump($v);
            }
            $this->assertTrue($b, 'associate array');
        }

        // array
        $array = [
            [123, 45, 657],
            ['ser', 'er3'],
            [0 => 'se', 1 => 'er', 2 => 'tr'],
        ];

        foreach ($array as $v) {
            $b = BreezeWriter::is_assoc($v);
            if ($b) {
                var_dump($v);
            }
            $this->assertFalse($b, 'associate array');
        }
    }

    private function getTestMsg()
    {
        $msg = new TestMsg();
        $msg->setMyInt(1234);
        $msg->setMyString('ewjo3**#J');

        $subMsg = new TestSubMsg();
        $subMsg->setMyString('J(*#^H');
        $subMsg->setMyInt(-345);
        $subMsg->setMyBool(true);
        $subMsg->setMyByte(36);
        $subMsg->setMyBytes(pack('N', 2435));
        $subMsg->setMyFloat32(3);
        $subMsg->setMyFloat64(7);
        $subMsg->setMyInt64(723847289347398);
        $subMsg->setMyArray([234, 5467, -678, 0]);
        $subMsg->setMyMap1(['j(*&*(' => 'fj98A)', 'J()*#' => pack('l', -4578)]);
        $subMsg->setMyMap2([234 => [-45, 0], 3465 => [0, 345]]);

        $msg->setMyArray([$subMsg]);
        $msg->setMyMap(['J(*U' => $subMsg]);
        return $msg;
    }
}
