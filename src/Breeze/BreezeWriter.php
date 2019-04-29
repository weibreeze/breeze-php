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
use Breeze\Types\TypeMap;
use Breeze\Types\TypeUnknown;
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
        if ($value == true) {
            $buf->writeByte(Type::N_TRUE);
        } else {
            $buf->writeByte(Type::N_FALSE);
        }
    }

    public static function writeString(Buffer $buf, $value)
    {
        $buf->writeByte(Type::N_STRING);
        $buf->writeZigzag(strlen($value));
        $buf->write($value);
    }

    public static function writeByte(Buffer $buf, $value)
    {
        $buf->writeByte(Type::N_BYTE);
        $buf->writeByte($value);
    }

    public static function writeBytes(Buffer $buf, $value)
    {
        $buf->writeByte(Type::N_BYTES);
        $buf->writeInt32(strlen($value));
        $buf->write($value);
    }

    public static function writeInt16(Buffer $buf, $value)
    {
        $buf->writeByte(Type::N_INT16);
        $buf->writeInt16($value);
    }

    public static function writeInt32(Buffer $buf, $value)
    {
        $buf->writeByte(Type::N_INT32);
        $buf->writeZigzag($value);
    }

    public static function writeInt64(Buffer $buf, $value)
    {
        $buf->writeByte(Type::N_INT64);
        $buf->writeZigzag($value);
    }

    public static function writeFloat32(Buffer $buf, $value)
    {
        $buf->writeByte(Type::N_FLOAT32);
        $buf->writeFloat32($value);
    }

    public static function writeFloat64(Buffer $buf, $value)
    {
        $buf->writeByte(Type::N_FLOAT64);
        $buf->writeFloat64($value);
    }

    public static function writeArray(Buffer $buf, array $value, Type $type = null)
    {
        $buf->writeByte(Type::N_ARRAY);
        $temp = new Buffer();
        foreach ($value as $v) {
            if (!is_null($v)) {
                self::writeValue($temp, $v, $type);
            }
        }
        $buf->appendWithLen($temp);
    }

    public static function writeMap(Buffer $buf, array $value, Type $kType = null, Type $vType = null)
    {
        $buf->writeByte(Type::N_MAP);
        $temp = new Buffer();
        foreach ($value as $k => $v) {
            if (!is_null($k) && !is_null($v)) {
                self::writeValue($temp, $k, $kType);
                self::writeValue($temp, $v, $vType);
            }
        }
        $buf->appendWithLen($temp);
    }

    /**
     * write all message fields according to readFieldsFunc.
     * @param Buffer $buf
     * @param string $name . message name
     * @param function $writeFieldFunc . writeFieldFunc(Buffer $buf)
     */
    public static function writeMessage(Buffer $buf, $name, callable $writeFieldFunc)
    {
        $buf->writeByte(Type::N_MESSAGE);
        self::writeString($buf, $name);
        $temp = new Buffer();
        $writeFieldFunc($temp);
        $buf->appendWithLen($temp);
    }

    /**
     * write a message field into breeze buf if field is not null.
     * @param Buffer $buf
     * @param int $index the index of message field.
     * @param mixed $value the value of message field.
     * @param Type $type the type of message field.
     * @throws BreezeException
     */
    public static function writeMessagField(Buffer $buf, $index, $value, Type $type = null)
    {
        if (is_null($value) || (is_array($value) && count($value) == 0)) {// null or empty array do not write to buffer.
            return;
        }
        $buf->writeZigzag($index);
        self::writeValue($buf, $value, $type);
    }

    /**
     * write a value into breeze buffer.
     * NOTICE:
     * if type is null, the value type will decided by function checkType(). it may not right for other language
     * @param Buffer $buf
     * @param mixed $value . it will write into buffer even the value is null.
     * @throws BreezeException :if the value type can not support by breeze ,the BreezeException is thrown
     */
    public static function writeValue(Buffer $buf, $value, Type $type = null)
    {
        if (is_null($value) || (is_array($value) && count($value) == 0)) {// null or empty array
            $buf->writeByte(Type::N_NULL);
            return;
        }
        if ($value instanceof Message) {
            self::writeByMessage($buf, $value);
            return;
        }
        // TODO extension while checkType is TypeUnknown
        self::writeWithType($buf, $value, is_null($type) ? self::checkType($value) : $type);
    }

    // param $type must not null.
    private static function writeWithType(Buffer $buf, $value, Type $type)
    {
        switch ($type->getTypeNum()) {
            case Type::N_TRUE: // TypeBool's numer is Type::N_TRUE
                self::writeBool($buf, $value);
                return;
            case Type::N_STRING:
                self::writeString($buf, $value);
                return;
            case Type::N_BYTE:
                self::writeByte($buf, $value);
                return;
            case Type::N_BYTES:
                self::writeBytes($buf, $value);
                return;
            case Type::N_INT16:
                self::writeInt16($buf, $value);
                return;
            case Type::N_INT32:
                self::writeInt32($buf, $value);
                return;
            case Type::N_INT64:
                self::writeInt64($buf, $value);
                return;
            case Type::N_FLOAT32:
                self::writeFloat32($buf, $value);
                return;
            case Type::N_FLOAT64:
                self::writeFloat64($buf, $value);
                return;
            case Type::N_ARRAY:
                self::writeArray($buf, $value, $type->getElemType());
                return;
            case Type::N_MAP:
                self::writeMap($buf, $value, $type->getElemType()[0], $type->getElemType()[1]);
                return;
            case Type::N_MESSAGE:
                self::writeByMessage($buf, $value);
                return;
            default:
                throw new BreezeException('not support by breeze. type: ' . $type->getTypeNum());
        }
    }

    private static function writeByMessage(Buffer $buf, Message $message)
    {
        $message->writeTo($buf);
    }

    private static function checkType($value)
    {
        if (is_bool($value)) {
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
                foreach ($value as $k => $v) {
                    return new TypeMap(self::checkType($k), self::checkType($v));
                }
            } else { // array
                return new TypeArray(self::checkType(current($value)));
            }
        }
        return TypeUnknown::instance();

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