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
use Breeze\Types\TypeBool;
use Breeze\Types\TypeByte;
use Breeze\Types\TypeBytes;
use Breeze\Types\TypeFloat32;
use Breeze\Types\TypeFloat64;
use Breeze\Types\TypeInt16;
use Breeze\Types\TypeInt32;
use Breeze\Types\TypeInt64;
use Breeze\Types\TypeMap;
use Breeze\Types\TypeMessage;
use Breeze\Types\TypePackedArray;
use Breeze\Types\TypePackedMap;
use Breeze\Types\TypeString;

/**
 * read value from breeze buffer.
 * @author: zhanglei
 *  Created at: 2019-04-30
 */
class BreezeReader
{
    /**
     * read all message fields according to readFieldsFunc.
     * @param Buffer $buf
     * @param callable $readFieldsFunc . readFieldsFunc(Buffer $buf, int $index)
     * @throws BreezeException
     */
    public static function readMessage(Buffer $buf, callable $readFieldsFunc)
    {
        $total = $buf->readInt32();
        if ($total > 0) {
            $end = $buf->pos() + $total;
            while ($buf->pos() < $end) {
                $index = $buf->readVarInt();
                $readFieldsFunc($buf, $index);
            }
            if ($buf->pos() != $end) {
                throw new BreezeException('Breeze: read byte size not correct');
            }
        }
    }

    /**
     * read any value.
     * @param Buffer $buf
     * @return mixed
     * @throws BreezeException
     */
    public static function readValue(Buffer $buf)
    {
        $tp = $buf->readByte();
        if ($tp == Type::T_NULL) {
            return null;
        } elseif ($tp < Type::T_NULL) {
            $result = self::readDirectBasic($buf, $tp);
            if (!is_null($result)) {
                return $result;
            }
        } elseif ($tp == Type::T_TRUE) {
            return true;
        } elseif ($tp == Type::T_FALSE) {
            return false;
        }
        $type = self::getType($buf, $tp);
        return $type->read($buf, false);
    }

    public static function skipType(Buffer $buf)
    {
        $tp = $buf->readByte();
        if ($tp >= Type::T_MESSAGE) {
            self::readMessageNameByType($buf, $tp);
        }
    }

    public static function readType(Buffer $buf)
    {
        $tp = $buf->readByte();
        return self::getType($buf, $tp);
    }

    public static function getType(Buffer $buf, $tp)
    {
        if ($tp >= Type::T_MESSAGE) {
            $name = self::readMessageNameByType($buf, $tp);
            $msg = Breeze::getMessage($name);
            if (is_null($msg)) {
                $msg = new GenericMessage($name);
            }
            return new TypeMessage($msg);
        }
        switch ($tp) {
            case Type::T_STRING:
                return TypeString::instance();
            case Type::T_INT32:
                return TypeInt32::instance();
            case Type::T_INT64:
                return TypeInt64::instance();
            case Type::T_TRUE:
                return TypeBool::instance();
            case Type::T_PACKED_ARRAY:
                return new TypePackedArray();
            case Type::T_PACKED_MAP:
                return new TypePackedMap();
            case Type::T_FLOAT32:
                return TypeFloat32::instance();
            case Type::T_FLOAT64:
                return TypeFloat64::instance();
            case Type::T_ARRAY:
                return TypeArray::instance();
            case Type::T_MAP:
                return TypeMap::instance();
            case Type::T_BYTE:
                return TypeByte::instance();
            case Type::T_BYTES:
                return TypeBytes::instance();
            case Type::T_INT16:
                return TypeInt16::instance();
        }
        throw new BreezeException('unknown breeze type:' . $tp);
    }

    public static function readMessageNameByType(Buffer $buf, $tp)
    {
        if ($tp == Type::T_MESSAGE) {
            $name = TypeString::readString($buf);
            $buf->getContext()->putMessageType($name);
            return $name;
        } else {
            if ($tp == Type::T_REF_MESSAGE) {
                $index = $buf->readVarInt();
            } else {
                $index = $tp - Type::T_REF_MESSAGE;
            }
            return $buf->getContext()->getMessageTypeName($index);
        }
    }

    public static function readDirectBasic(Buffer $buf, $tp)
    {
        if (TypeString::isDirectString($tp)) {
            return TypeString::readStringBySize($buf, $tp);
        } elseif (TypeInt32::isDirectInt32($tp)) {
            return TypeInt32::getDirectInt32($tp);
        } elseif (TypeInt64::isDirectInt64($tp)) {
            return TypeInt64::getDirectInt64($tp);
        }
        return null;
    }
}