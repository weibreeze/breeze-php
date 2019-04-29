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
use Breeze\Types\TypeByte;
use Breeze\Types\TypeMessage;

/**
 * MessageField contains a value and a type.
 * it will check value's type when set value into MessageField.
 * @author: zhanglei
 *  Created at: 2019-04-30
 */
class MessageField
{
    private $value;
    private $fieldDesc;

    public function __construct(FieldDesc $field)
    {
        if (is_null($field)) {
            throw new BreezeException('MessageField must has FieldDesc!');
        }
        $this->fieldDesc = $field;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value . value must has right type or null.
     * @throws BreezeException
     */
    public function setValue($value)
    {
        if (!is_null($value) && !self::checkType($value, $this->fieldDesc->getType())) {
            throw new BreezeException('param type not correct for breeze message setter');
        }
        $this->value = $value;
    }

    public function getFieldDesc()
    {
        return $this->fieldDesc;
    }

    public static function checkType($value, Type $type = null)
    {
        if (is_null($type)) {
            return true;
        }
        switch ($type->getTypeNum()) {
            case Type::N_TRUE:
            case Type::N_FALSE:
                return is_bool($value);
            case Type::N_BYTE:
                return self::checkByte($value);
            case Type::N_INT16:
                return self::checkInt16($value);
            case Type::N_INT32:
                return self::checkInt32($value);
            case Type::N_INT64:
            case Type::N_FLOAT32:
            case Type::N_FLOAT64:// only check numeric
                return is_numeric($value);
            case Type::N_STRING:
                return !is_array($value) && !is_object($value);
            case Type::N_ARRAY:
                return self::checkArray($value, $type->getElemType());
            case Type::N_MAP:
                return self::checkMap($value, $type->getElemType()[0], $type->getElemType()[1]);
            case Type::N_MESSAGE:
                return self::checkMessage($value, $type);
        }
        return true;
    }

    private static function checkByte($value)
    {
        if (is_string($value) && strlen($value) == 1) {
            return true;
        } else if (is_int($value) && $value >= -128 && $value <= 127) {
            return true;
        }
        return false;
    }

    private static function checkInt16($value)
    {
        return is_int($value) && $value >= -32768 && $value <= 32767;
    }

    private static function checkInt32($value)
    {
        return is_int($value) && $value >= -2147483648 && $value <= 2147483647;
    }

    private static function checkArray($value, Type $elemType = null)
    {
        if (is_array($value) && (empty($value) || !BreezeWriter::is_assoc($value))) {
            foreach ($value as $v) {
                if (!self::checkType($v, $elemType)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    private static function checkMap($value, Type $kType = null, Type $vType = null)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                if (!self::checkType($k, $kType) || !self::checkType($v, $vType)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    private static function checkMessage($value, TypeMessage $type)
    {
        return ($value instanceof Message) && $value->getName() === $type->getDefault()->getName();
    }
}