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
use Breeze\Types\TypeMessage;

/**
 * read value from breeze buffer.
 * @author: zhanglei
 *  Created at: 2019-04-30
 */
class BreezeReader
{
    /**
     * read a field into MessageField.
     * @param Buffer $buf
     * @param MessageField $field
     * @throws BreezeException
     */
    public static function readField(Buffer $buf, MessageField &$field)
    {
        $field->setValue(self::readValue($buf, $field->getFieldDesc()->getType()));
    }

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
                $index = $buf->readZigzag();
                $readFieldsFunc($buf, $index);
            }
            if ($buf->pos() != $end) {
                throw new BreezeException('Breeze: read byte size not correct');
            }
        }
    }

    /**
     * read value by type.
     * @param Buffer $buf
     * @param Type|null $type
     * @return mixed
     * @throws BreezeException
     */
    public static function readValue(Buffer $buf, Type $type = null)
    {
        $t = $buf->readByte();
        switch ($t) {
            case Type::N_TRUE:
                if (is_null($type) || $type->getTypeNum() == Type::N_TRUE) {
                    return true;
                }
                break;
            case Type::N_FALSE:
                if (is_null($type) || $type->getTypeNum() == Type::N_TRUE) {
                    return false;
                }
                break;
            case Type::N_STRING:
                return self::readStringWithoutTag($buf, $type);
            case Type::N_BYTE:
                return self::readByteWithoutTag($buf, $type);
            case Type::N_BYTES:
                return self::readBytesWithoutTag($buf, $type);
            case Type::N_INT16:
                return self::readInt16WithoutTag($buf, $type);
            case Type::N_INT32:
                return self::readInt32WithoutTag($buf, $type);
            case Type::N_INT64:
                return self::readInt64WithoutTag($buf, $type);
            case Type::N_FLOAT32:
                return self::readFlaot32WithoutTag($buf, $type);
            case Type::N_FLOAT64:
                return self::readFloat64WithoutTag($buf, $type);
            case Type::N_ARRAY:
                return self::readArrayWithoutTag($buf, $type);
            case Type::N_MAP:
                return self::readMapWithoutTag($buf, $type);
            case Type::N_MESSAGE:
                return self::readMessageWithoutTag($buf, $type);
            default:
                throw new BreezeException('unknown breeze type. expect type: ' . $type->getTypeNum() . ', real type:' . $t);

        }
        throw new BreezeException('not support by breeze. expect type: ' . $type->getTypeNum() . ', real type:' . $t);
    }

    private static function readStringWithoutTag(Buffer $buf, Type $type = null)
    {
        $len = $buf->readZigzag();
        return self::cast($buf->read($len), $type);
    }

    private static function readByteWithoutTag(Buffer $buf, Type $type = null)
    {
        return self::cast($buf->readByte(), $type);
    }

    private static function readBytesWithoutTag(Buffer $buf, Type $type = null)
    {
        $len = $buf->readInt32();
        return self::cast($buf->read($len), $type);
    }

    private static function readInt16WithoutTag(Buffer $buf, Type $type = null)
    {
        return self::cast($buf->readInt16(), $type);
    }

    private static function readInt32WithoutTag(Buffer $buf, Type $type = null)
    {
        return self::cast($buf->readZigzag(), $type);
    }

    private static function readInt64WithoutTag(Buffer $buf, Type $type = null)
    {
        return self::cast($buf->readZigzag(), $type);
    }

    private static function readFlaot32WithoutTag(Buffer $buf, Type $type = null)
    {
        return self::cast($buf->readFloat32(), $type);
    }

    private static function readFloat64WithoutTag(Buffer $buf, Type $type = null)
    {
        return self::cast($buf->readFloat64(), $type);
    }

    private static function readArrayWithoutTag(Buffer $buf, Type $type = null)
    {
        $result = array();
        $total = $buf->readInt32();
        if ($total > 0) {
            $elemType = null;
            if (!is_null($type) && $type instanceof TypeArray) {
                $elemType = $type->getElemType();
            }
            $end = $buf->pos() + $total;
            while ($buf->pos() < $end) {
                $result[] = self::readValue($buf, $elemType);
            }
            if ($buf->pos() != $end) {
                throw new BreezeException('Breeze: read byte size not correct');
            }
        }
        return $result;
    }

    private static function readMapWithoutTag(Buffer $buf, Type $type = null)
    {
        $result = array();
        $total = $buf->readInt32();
        if ($total > 0) {
            $kType = null;
            $vType = null;
            if (!is_null($type) && $type instanceof TypeMap) {
                $kType = $type->getElemType()[0];
                $vType = $type->getElemType()[1];
            }
            $end = $buf->pos() + $total;
            while ($buf->pos() < $end) {
                $result[self::readValue($buf, $kType)] = self::readValue($buf, $vType);
            }
            if ($buf->pos() != $end) {
                throw new BreezeException('Breeze: read byte size not correct');
            }
        }
        return $result;
    }

    private static function readMessageWithoutTag(Buffer $buf, Type $type = null)
    {
        $t = $buf->readByte();
        if ($t != Type::N_STRING) {
            throw new BreezeException('worng breeze message format. need name type 3');
        }
        $name = self::readStringWithoutTag($buf);
        $message = null;
        if (is_null($type)) {
            $message = Breeze::getMessage($name);
        } else {
            if (!$type instanceof TypeMessage) {
                throw new BreezeException('can not read breeze message:' . $name . ' to type:' . $type->getTypeNum());
            }
            $message = $type->getDefault();
        }
        if (is_null($message)) { // use GenericMessage as default if not find message.
            $message = new GenericMessage($name);
        }
        $message->readFrom($buf);
        return $message;
    }

    private static function cast($value, Type $type = null)
    {
        if (is_null($type)) {
            return $value;
        }
        switch ($type->getTypeNum()) {
            case Type::N_TRUE:
            case Type::N_FALSE:
                return (bool)$value;
            case Type::N_STRING:
            case Type::N_BYTES:
                return (string)$value;
            case Type::N_BYTE:
            case Type::N_INT16:
            case Type::N_INT32:
            case Type::N_INT64:
                return (int)$value;
            case Type::N_FLOAT32:
            case Type::N_FLOAT64:
                return (float)$value;
        }
        throw new BreezeException('can not convert to type: ' . $type->getTypeNum() . ', real type:' . gettype($value));
    }
}