<?php

namespace Breeze;

use Breeze\Test\TestMsg;
use Breeze\Test\TestStruct;
use Breeze\Test\TestSubMsg;
use Breeze\Types\TypeArray;
use Breeze\Types\TypeBool;
use Breeze\Types\TypeByte;
use Breeze\Types\TypeFloat32;
use Breeze\Types\TypeFloat64;
use Breeze\Types\TypeInt16;
use Breeze\Types\TypeInt32;
use Breeze\Types\TypeInt64;
use Breeze\Types\TypeMap;
use Breeze\Types\TypeMessage;
use Breeze\Types\TypeString;
use PHPUnit\Framework\TestCase;

class MessageFieldTest extends TestCase
{
    public function testCheckByte()
    {
        // right values
        $array = [
            // byte
            new TestStruct(123, TypeByte::instance()),
            new TestStruct(0, TypeByte::instance()),
            new TestStruct(-8, TypeByte::instance()),
            new TestStruct(127, TypeByte::instance()),
            new TestStruct(-128, TypeByte::instance()),
            new TestStruct('A', TypeByte::instance()),
            new TestStruct('g', TypeByte::instance()),
            new TestStruct("\n", TypeByte::instance()),
        ];
        $this->check($array);

        //wrong values
        $array = [
            new TestStruct(128, TypeByte::instance()),
            new TestStruct(-129, TypeByte::instance()),
            new TestStruct(256, TypeByte::instance()),
            new TestStruct('jke', TypeByte::instance()),
            new TestStruct('89', TypeByte::instance()),
            new TestStruct('\n', TypeByte::instance()),
            new TestStruct(3.14, TypeByte::instance()),
            new TestStruct(true, TypeByte::instance()),
        ];
        $this->check($array, false);
    }

    public function testCheckBool()
    {
        // right values
        $array = [
            new TestStruct(true, TypeBool::instance()),
            new TestStruct(false, TypeBool::instance()),
        ];
        $this->check($array);

        //wrong values
        $array = [
            new TestStruct('ser', TypeBool::instance()),
            new TestStruct(23, TypeBool::instance()),
            new TestStruct(3.14, TypeBool::instance()),
        ];
        $this->check($array, false);
    }

    public function testCheckInt16()
    {
        // right values
        $array = [
            new TestStruct(0, TypeInt16::instance()),
            new TestStruct(1234, TypeInt16::instance()),
            new TestStruct(-342, TypeInt16::instance()),
            new TestStruct(-32768, TypeInt16::instance()),
            new TestStruct(32767, TypeInt16::instance()),
        ];
        $this->check($array);

        //wrong values
        $array = [
            new TestStruct(-32769, TypeInt16::instance()),
            new TestStruct(32768, TypeInt16::instance()),
            new TestStruct('ser', TypeInt16::instance()),
            new TestStruct(true, TypeInt16::instance()),
            new TestStruct(3.14, TypeInt16::instance()),
        ];
        $this->check($array, false);
    }

    public function testCheckInt32()
    {
        // right values
        $array = [
            new TestStruct(0, TypeInt32::instance()),
            new TestStruct(1234, TypeInt32::instance()),
            new TestStruct(-342, TypeInt32::instance()),
            new TestStruct(-32769, TypeInt32::instance()),
            new TestStruct(32768, TypeInt32::instance()),
            new TestStruct(-2147483648, TypeInt32::instance()),
            new TestStruct(2147483647, TypeInt32::instance()),
        ];
        $this->check($array);

        //wrong values
        $array = [
            new TestStruct(-2147483649, TypeInt32::instance()),
            new TestStruct(2147483648, TypeInt32::instance()),
            new TestStruct('ser', TypeInt32::instance()),
            new TestStruct(true, TypeInt32::instance()),
            new TestStruct(3.14, TypeInt32::instance()),
        ];
        $this->check($array, false);
    }

    public function testString()
    {
        // right values
        $array = [
            new TestStruct(0, TypeString::instance()),
            new TestStruct(1234, TypeString::instance()),
            new TestStruct(-342, TypeString::instance()),
            new TestStruct('sjdlkj23.KJ(I*(UJL;', TypeString::instance()),
            new TestStruct('\n', TypeString::instance()),
            new TestStruct('', TypeString::instance()),
            new TestStruct(3.14, TypeString::instance()),
        ];
        $this->check($array);

        //wrong values
        $array = [
            new TestStruct(['232', 'sadr', 'MIJUe'], TypeString::instance()),
            new TestStruct(['wer' => 34], TypeString::instance()),
            new TestStruct(new Schema(), TypeString::instance()),
        ];
        $this->check($array, false);
    }

    public function testCheckIntAndFloat()
    {
        $types = [TypeInt64::instance(), TypeFloat32::instance(), TypeFloat64::instance()];
        foreach ($types as $t) {
            // right values
            $array = [
                new TestStruct(0, $t),
                new TestStruct(1234, $t),
                new TestStruct(-342, $t),
                new TestStruct(-32769, $t),
                new TestStruct(32768, $t),
                new TestStruct(-2147483648, $t),
                new TestStruct(2147483647, $t),
                new TestStruct(-2147483649, $t),
                new TestStruct(2147483648, $t),
                new TestStruct(3.14, $t),
                new TestStruct(-3.14, $t),
                new TestStruct(237489.1434, $t),
                new TestStruct(-98234237489.1434789023, $t),
                new TestStruct('789', $t),
                new TestStruct('-789', $t),
                new TestStruct('-234.789', $t),
                new TestStruct('7.2349', $t),
            ];
            $this->check($array);

            //wrong values
            $array = [
                new TestStruct('ser', $t),
                new TestStruct('', $t),
                new TestStruct(true, $t),
            ];
            $this->check($array, false);
        }
    }

    public function testCheckArray()
    {
        // right values
        $array = [
            new TestStruct([123, 35, 67, 78, 0, -2134, -34], new TypeArray(TypeInt16::instance())),
            new TestStruct([123.34, 35.547, 67, 78, 0, -2134, -34], new TypeArray(TypeFloat32::instance())),
            new TestStruct([['xxx' => 16, 'de' => -8], ['\n\r\t' => 0]], new TypeArray(new TypeMap(TypeString::instance(), TypeByte::instance()))),
            new TestStruct([new TestMsg(), new TestMsg()], new TypeArray(new TypeMessage(new TestMsg()))),
        ];
        $this->check($array);

        //wrong values
        $array = [
            new TestStruct([123, 35, 67, 234566, 0, -2134, -34], new TypeArray(TypeInt16::instance())),
            new TestStruct([123.34, 35.547, 67, '\n', 0, -2134, -34], new TypeArray(TypeFloat32::instance())),
            new TestStruct([['xxx' => 16, 'de' => 'uiou*('], ['\n\r\t' => 0]], new TypeArray(new TypeMap(TypeString::instance(), TypeByte::instance()))),
            new TestStruct([new TestMsg(), 123], new TypeArray(new TypeMessage(new TestMsg()))),

        ];
        $this->check($array, false);
    }

    public function testCheckMap()
    {
        // right values
        $array = [
            new TestStruct(['sji(*&' => 123, 'DLO@' => 35, 'OI)#' => 67, '' => 78], new TypeMap(TypeString::instance(), TypeInt16::instance())),
            new TestStruct(['jOI&(*#)_' => [123.34, 35.547], 'jd>/KO(*#)_' => [34, 547]], new TypeMap(TypeString::instance(), new TypeArray(TypeFloat32::instance()))),
            new TestStruct(['xxx' => new TestMsg(), 'de' => new TestMsg()], new TypeMap(TypeString::instance(), new TypeMessage(new TestMsg()))),
        ];
        $this->check($array);

        //wrong values
        $array = [
            new TestStruct(['sji(*&' => 123, 'DLO@' => 'jOI*&()', 'OI)#' => 67, '' => 78], new TypeMap(TypeString::instance(), TypeInt16::instance())),
            new TestStruct(['jOI&(*#)_' => [123.34, 35.547], 'jd>/KO(*#)_' => [34, 'KPOIU)(']], new TypeMap(TypeString::instance(), new TypeArray(TypeFloat32::instance()))),
            new TestStruct(['xxx' => new TestMsg(), 'de' => 2134], new TypeMap(TypeString::instance(), new TypeMessage(new TestMsg()))),
        ];
    }

    public function testCheckMessage()
    {
        // right values
        $array = [
            new TestStruct(new TestMsg(), new TypeMessage(new TestMsg())),
            new TestStruct(new TestSubMsg(), new TypeMessage(new TestSubMsg())),
        ];
        $this->check($array);

        //wrong values
        $array = [
            new TestStruct(new TestStruct("se", 'wer'), new TypeMessage(new TestMsg())),
            new TestStruct('joi*)(UJ3', new TypeMessage(new TestSubMsg())),
        ];
        $this->check($array, false);
    }

    private function check($array, $return = true)
    {
        foreach ($array as $struct) {
            $ret = MessageField::checkType($struct->v, $struct->t);
            $this->assertEquals($return, $ret, 'check type:' . (is_object($struct->v) || is_array($struct->v)) ? gettype($struct->v) : $struct->v);
        }
    }
}