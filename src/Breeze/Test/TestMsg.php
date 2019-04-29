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

use Breeze\AbstraceMessage;
use Breeze\BreezeReader;
use Breeze\BreezeWriter;
use Breeze\Buffer;
use Breeze\FieldDesc;
use Breeze\MessageField;
use Breeze\Schema;
use Breeze\Types\TypeArray;
use Breeze\Types\TypeInt32;
use Breeze\Types\TypeMap;
use Breeze\Types\TypeMessage;
use Breeze\Types\TypeString;

/**
 * for test.
 * @author: zhanglei
 *  Created at: 2019-04-30
 */
class TestMsg extends AbstraceMessage
{
    private $int;
    private $string;
    private $map;
    private $array;

    private static $schema;

    public function __construct()
    {
        if (is_null(self::$schema)) {
            $this->initSchema();
        }
        $this->int = new MessageField(self::$schema->getField(1));
        $this->string = new MessageField(self::$schema->getField(2));
        $this->map = new MessageField(self::$schema->getField(3));
        $this->array = new MessageField(self::$schema->getField(4));
    }

    private function initSchema()
    {
        $schema = new Schema();
        $schema->setName("weibo-TestMsg");
        $schema->putField(new FieldDesc(1, 'int', TypeInt32::instance()));
        $schema->putField(new FieldDesc(2, 'string', TypeString::instance()));
        $schema->putField(new FieldDesc(3, 'map', new TypeMap(TypeString::instance(), new TypeMessage(new TestSubMsg()))));
        $schema->putField(new FieldDesc(4, 'array', new TypeArray(new TypeMessage(new TestSubMsg()))));
        self::$schema = $schema;
    }

    public function defaultInstance()
    {
        return new TestMsg();
    }

    public function writeTo(Buffer $buf)
    {
        BreezeWriter::writeMessage($buf, $this->getName(), function (Buffer $fbuf) {
            $this->writeFields($fbuf, $this->int, $this->string, $this->map, $this->array);
        });
    }

    public function readFrom(Buffer $buf)
    {
        BreezeReader::readMessage($buf, function (Buffer $fbuf, $index) {
            switch ($index) {
                case 1:
                    return BreezeReader::readField($fbuf, $this->int);
                case 2:
                    return BreezeReader::readField($fbuf, $this->string);
                case 3:
                    return BreezeReader::readField($fbuf, $this->map);
                case 4:
                    return BreezeReader::readField($fbuf, $this->array);
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

    public function getInt()
    {
        return $this->int->getValue();
    }

    public function setInt($value)
    {
        $this->int->setValue($value);
        return $this;
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

    public function getMap()
    {
        return $this->map->getValue();
    }

    public function setMap($value)
    {
        $this->map->setValue($value);
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
}