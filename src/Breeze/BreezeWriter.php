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


namespace Breeze;

use Breeze\Types\Type;
use Breeze\Types\TypeArray;
use Breeze\Types\TypeByte;
use Breeze\Types\TypeBytes;
use Breeze\Types\TypeFloat32;
use Breeze\Types\TypeInt16;
use Breeze\Types\TypeInt32;
use Breeze\Types\TypeMap;
use Breeze\Types\TypeMessage;
use Breeze\Types\TypePackedArray;
use Breeze\Types\TypePackedMap;
use Breeze\Types\TypeBool;
use Breeze\Types\TypeFloat64;
use Breeze\Types\TypeInt64;
use Breeze\Types\TypeString;

/**
 * write value into breeze buffer.
 * @author: zhanglei
 *  Created at: 2019-04-30
 */
class BreezeWriter
{
    public static function writeBool(Buffer $buf, $value)
    {
        TypeBool::instance()->write($buf, $value);
    }

    public static function writeString(Buffer $buf, $value)
    {
        TypeString::instance()->write($buf, $value);
    }

    public static function writeByte(Buffer $buf, $value)
    {
        TypeByte::instance()->write($buf, $value);
    }

    public static function writeBytes(Buffer $buf, $value)
    {
        TypeBytes::instance()->write($buf, $value);
    }

    public static function writeInt16(Buffer $buf, $value)
    {
        TypeInt16::instance()->write($buf, $value);
    }

    public static function writeInt32(Buffer $buf, $value)
    {
        TypeInt32::instance()->write($buf, $value);
    }

    public static function writeInt64(Buffer $buf, $value)
    {
        TypeInt64::instance()->write($buf, $value);
    }

    public static function writeFloat32(Buffer $buf, $value)
    {
        TypeFloat32::instance()->write($buf, $value);
    }

    public static function writeFloat64(Buffer $buf, $value)
    {
        TypeFloat64::instance()->write($buf, $value);
    }

    /**
     * @param Buffer $buf
     * @param array $value
     * @param Type|null $type element type
     * @throws BreezeException
     */
    public static function writeArray(Buffer $buf, array $value, Type $type = null)
    {
        if (!is_null($type)) {
            $packedArray = new TypePackedArray($type);
            $packedArray->write($buf, $value);
        } else {
            $packedArray = self::checkType($value);
            $packedArray->write($buf, $value);
        }
    }

    public static function writeMap(Buffer $buf, array $value, Type $kType = null, Type $vType = null)
    {
        if (!is_null($kType) && !is_null($vType)) {
            $packedMap = new TypePackedMap($kType, $vType);
            $packedMap->write($buf, $value);
        } else {
            $packedMap = self::checkType($value);
            $packedMap->write($buf, $value);
        }
    }

    /**
     * write all message fields according to writeFieldsFunc.
     * @param Buffer $buf
     * @param callable $writeFieldFunc . writeFieldFunc(Buffer $buf)
     */
    public static function writeMessage(Buffer $buf, callable $writeFieldFunc)
    {
        $temp = $buf->newSubBuffer();
        $writeFieldFunc($temp);
        $buf->appendWithLen($temp);
    }

    public static function writeMessageType(Buffer $buf, $name)
    {
        $index = $buf->getContext()->getMessageTypeIndex($name);
        if (is_null($index)) {
            $buf->writeByte(Type::T_MESSAGE);
            TypeString::instance()->write($buf, $name, false);
            $buf->getContext()->putMessageType($name);
        } else {
            if ($index > Type::DIRECT_REF_MESSAGE_MAX_VALUE) {
                $buf->writeByte(Type::T_REF_MESSAGE);
                $buf->writeVarInt($index);
            } else {
                $buf->writeByte(Type::T_REF_MESSAGE + $index);
            }
        }
    }

    /**
     * write a message field into breeze buf if field is not null.
     * @param Buffer $buf
     * @param int $index the index of message field.
     * @param mixed $value the value of message field.
     * @param Type $type the type of message field.
     */
    public static function writeMessageField(Buffer $buf, $index, $value, Type $type)
    {
        if (is_null($value) || (is_array($value) && count($value) == 0)) {// null or empty array do not write to buffer.
            return;
        }
        $buf->writeVarInt($index);
        $type->write($buf, $value);
    }

    /**
     * write a value into breeze buffer.
     * NOTICE:
     * if type is null, the value type will decided by function checkType(). it may not right for other language
     * @param Buffer $buf
     * @param mixed $value . it will write into buffer even the value is null.
     * @param Type|null $type
     * @throws BreezeException :if the value type can not support by breeze ,the BreezeException is thrown
     */
    public static function writeValue(Buffer $buf, $value, Type $type = null)
    {
        if (is_null($value) || (is_array($value) && count($value) == 0)) {// null or empty array
            $buf->writeByte(Type::T_NULL);
            return;
        }
        if (is_null($type)) {
            $type = self::checkType($value);
        }
        $type->write($buf, $value, true);
    }

    public static function checkType($value)
    {
        if ($value instanceof Message) {
            return new TypeMessage($value->defaultInstance());
        } elseif (is_bool($value)) {
            return TypeBool::instance();
        } elseif (is_string($value)) {
            return TypeString::instance();
        } elseif (is_int($value)) {
            return TypeInt64::instance();
        } elseif (is_float($value)) {
            return TypeFloat64::instance();
        } elseif (is_array($value)) {
            // must not empty
            if (self::is_assoc($value)) {// map
                if (Breeze::$IS_PACK) {
                    foreach ($value as $k => $v) {
                        return new TypePackedMap(self::checkType($k), self::checkType($v));
                    }
                } else {
                    return TypeMap::instance();
                }

            } else { // array
                if (Breeze::$IS_PACK) {
                    return new TypePackedArray(self::checkType(current($value)));
                } else {
                    return TypeArray::instance();
                }
            }
        }
        throw new BreezeException('can not check to breezetype. value: ' . $value);
    }

    /**
     * @param $arr
     * @return bool true:associate array or empty array, false: list
     */
    public static function is_assoc($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}