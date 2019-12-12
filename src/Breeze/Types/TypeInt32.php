<?php
/**
 * Copyright (c) 2009-2019. Weibo, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the 'License');
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *             http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an 'AS IS' BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */


namespace Breeze\Types;

use Breeze\BreezeException;
use Breeze\BreezeReader;
use Breeze\Buffer;

/**
 * type int32: 4 bytes
 * @author: zhanglei
 *  Created at: 2019-04-30
 */
class TypeInt32 implements Type
{
    private static $ins;

    private function __construct()
    {
    }

    public static function instance()
    {
        if (is_null(self::$ins)) {
            self::$ins = new TypeInt32();
        }
        return self::$ins;
    }

    public function read(Buffer $buf, $withType = true)
    {
        if (!$withType) {
            return $buf->readZigzag();
        }
        // with type
        $tp = $buf->readByte();
        if (self::isDirectInt32($tp)) {
            return self::getDirectInt32($tp);
        }
        switch ($tp) {
            case self::T_INT32:
            case self::T_INT64:
                return $buf->readZigzag();
            case self::T_INT16:
                return $buf->readInt16();
            case self::T_STRING:
                return (int)TypeString::readString($buf);
            default:
                $ret = BreezeReader::readDirectBasic($buf, $tp);
                if (!is_null($ret)) {
                    return (int)$ret;
                }
                throw new BreezeException('wrong breeze int32 type. type:' . $tp);
        }
    }

    public function write(Buffer $buf, $value, $withType = true)
    {
        if ($withType) {
            if ($value >= self::DIRECT_INT32_MIN_VALUE && $value <= self::DIRECT_INT32_MAX_VALUE) {
                $buf->writeByte($value + self::INT32_ZERO);
                return;
            }
            $buf->writeByte(self::T_INT32);
        }
        $buf->writeZigzag($value);
    }

    public function checkType($value)
    {
        return is_int($value) && $value >= -2147483648 && $value <= 2147483647;
    }

    public function writeType(Buffer $buf)
    {
        $buf->writeByte(self::T_INT32);
    }

    public static function isDirectInt32($type)
    {
        return $type >= self::T_DIRECT_INT32_MIN && $type <= self::T_DIRECT_INT32_MAX;
    }

    public static function getDirectInt32($type)
    {
        return $type - self::INT32_ZERO;
    }
}