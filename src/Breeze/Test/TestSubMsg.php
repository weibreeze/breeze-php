<?php
/**
 * Copyright (c) 2009-2019. Weibo, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *             http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */


namespace Breeze\Test;

use Breeze\AbstractMessage;
use Breeze\BreezeReader;
use Breeze\BreezeWriter;
use Breeze\Buffer;
use Breeze\FieldDesc;
use Breeze\MessageField;
use Breeze\Schema;
use Breeze\Types\TypeArray;
use Breeze\Types\TypeBool;
use Breeze\Types\TypeByte;
use Breeze\Types\TypeBytes;
use Breeze\Types\TypeFloat32;
use Breeze\Types\TypeFloat64;
use Breeze\Types\TypeInt32;
use Breeze\Types\TypeInt64;
use Breeze\Types\TypeMap;
use Breeze\Types\TypeString;

/**
 * for test
 * @author: zhanglei
 *  Created at: 2019-04-30
 */
class TestSubMsg extends AbstractMessage
{
    private $string;
    private $int;
    private $int64;
    private $float32;
    private $float64;
    private $byte;
    private $bytes;
    private $map1;
    private $map2;
    private $array;
    private $bool;

    private static $schema;

    public function __construct()
    {
        if (is_null(self::$schema)) {
            $this->initSchema();
        }
        $this->string = new MessageField(self::$schema->getField(1));
        $this->int = new MessageField(self::$schema->getField(2));
        $this->int64 = new MessageField(self::$schema->getField(3));
        $this->float32 = new MessageField(self::$schema->getField(4));
        $this->float64 = new MessageField(self::$schema->getField(5));
        $this->byte = new MessageField(self::$schema->getField(6));
        $this->bytes = new MessageField(self::$schema->getField(7));
        $this->map1 = new MessageField(self::$schema->getField(8));
        $this->map2 = new MessageField(self::$schema->getField(9));
        $this->array = new MessageField(self::$schema->getField(10));
        $this->bool = new MessageField(self::$schema->getField(11));
    }

    private function initSchema()
    {
        $schema = new Schema('TestSubMsg');
        $schema->putField(new FieldDesc(1, 'string', TypeString::instance()));
        $schema->putField(new FieldDesc(2, 'int', TypeInt32::instance()));
        $schema->putField(new FieldDesc(3, 'int64', TypeInt64::instance()));
        $schema->putField(new FieldDesc(4, 'float32', TypeFloat32::instance()));
        $schema->putField(new FieldDesc(5, 'float64', TypeFloat64::instance()));
        $schema->putField(new FieldDesc(6, 'byte', TypeByte::instance()));
        $schema->putField(new FieldDesc(7, 'bytes', TypeBytes::instance()));
        $schema->putField(new FieldDesc(8, 'map1', new TypeMap(TypeString::instance(), TypeBytes::instance())));
        $schema->putField(new FieldDesc(9, 'map2', new TypeMap(TypeInt32::instance(), new TypeArray())));
        $schema->putField(new FieldDesc(10, 'array', new TypeArray(TypeInt32::instance())));
        $schema->putField(new FieldDesc(11, 'bool', TypeBool::instance()));
        self::$schema = $schema;
    }


    public function writeTo(Buffer $buf)
    {
        BreezeWriter::writeMessage($buf, $this->getName(), function (Buffer $fbuf) {
            $this->writeFields($fbuf, $this->string, $this->int, $this->int64, $this->float32, $this->float64,
                $this->byte, $this->bytes, $this->map1, $this->map2, $this->array, $this->bool);
        });
    }

    public function readFrom(Buffer $buf)
    {
        BreezeReader::readMessage($buf, function (Buffer $fbuf, $index) {
            switch ($index) {
                case 1:
                    return BreezeReader::readField($fbuf, $this->string);
                case 2:
                    return BreezeReader::readField($fbuf, $this->int);
                case 3:
                    return BreezeReader::readField($fbuf, $this->int64);
                case 4:
                    return BreezeReader::readField($fbuf, $this->float32);
                case 5:
                    return BreezeReader::readField($fbuf, $this->float64);
                case 6:
                    return BreezeReader::readField($fbuf, $this->byte);
                case 7:
                    return BreezeReader::readField($fbuf, $this->bytes);
                case 8:
                    return BreezeReader::readField($fbuf, $this->map1);
                case 9:
                    return BreezeReader::readField($fbuf, $this->map2);
                case 10:
                    return BreezeReader::readField($fbuf, $this->array);
                case 11:
                    return BreezeReader::readField($fbuf, $this->bool);
                default: // for compatibility
                    BreezeReader::readValue($fbuf);
            }
        });
    }

    public function getName()
    {
        return self::$schema->getName();
    }

    public function getAlias()
    {
        return self::$schema->getAlias();
    }

    public function getSchema()
    {
        return self::$schema;
    }

    public function defaultInstance()
    {
        return new TestSubMsg();
    }

    public function getString()
    {
        return $this->string->getValue();
    }

    public function setString($value)
    {
        $this->string->setValue($value);
        return $this;
    }

    public function getInt()
    {
        return $this->int->getValue();
    }

    public function setInt($value)
    {
        $this->int->setValue($value);
        return $this;
    }

    public function getInt64()
    {
        return $this->int64->getValue();
    }

    public function setInt64($value)
    {
        $this->int64->setValue($value);
        return $this;
    }

    public function getFloat32()
    {
        return $this->float32->getValue();
    }

    public function setFloat32($value)
    {
        $this->float32->setValue($value);
        return $this;
    }

    public function getFloat64()
    {
        return $this->float64->getValue();
    }

    public function setFloat64($value)
    {
        $this->float64->setValue($value);
        return $this;
    }

    public function getByte()
    {
        return $this->byte->getValue();
    }

    public function setByte($value)
    {
        $this->byte->setValue($value);
        return $this;
    }

    public function getBytes()
    {
        return $this->bytes->getValue();
    }

    public function setBytes($value)
    {
        $this->bytes->setValue($value);
        return $this;
    }

    public function getMap1()
    {
        return $this->map1->getValue();
    }

    public function setMap1($value)
    {
        $this->map1->setValue($value);
        return $this;
    }

    public function getMap2()
    {
        return $this->map2->getValue();
    }

    public function setMap2($value)
    {
        $this->map2->setValue($value);
        return $this;
    }

    public function getArray()
    {
        return $this->array->getValue();
    }

    public function setArray($value)
    {
        $this->array->setValue($value);
        return $this;
    }

    public function getBool()
    {
        return $this->bool->getValue();
    }

    public function setBool($value)
    {
        $this->bool->setValue($value);
        return $this;
    }
}