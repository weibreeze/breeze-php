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
 * type int64: 8 bytes
 * @author: zhanglei
 *  Created at: 2019-04-30
 */
class TypeInt64 implements Type
{
    private static $ins;

    private function __construct()
    {
    }

    public static function instance()
    {
        if (is_null(self::$ins)) {
            self::$ins = new TypeInt64();
        }
        return self::$ins;
    }

    public function read(Buffer $buf, $withType = true)
    {
        if (!$withType) {
            return $buf->readZigzag();
        }
        $tp = $buf->readByte();
        if (self::isDirectInt64($tp)) {
            return self::getDirectInt64($tp);
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
                throw new BreezeException('wrong breeze int64 type. type:' . $tp);
        }
    }

    public function write(Buffer $buf, $value, $withType = true)
    {
        if ($withType) {
            if ($value >= self::DIRECT_INT64_MIN_VALUE && $value <= self::DIRECT_INT64_MAX_VALUE) {
                $buf->writeByte($value + self::INT64_ZERO);
                return;
            }
            $buf->writeByte(self::T_INT64);
        }
        $buf->writeZigzag($value);
    }

    public function checkType($value)
    {
        return is_numeric($value);
    }

    public function writeType(Buffer $buf)
    {
        $buf->writeByte(self::T_INT64);
    }

    public static function isDirectInt64($type)
    {
        return $type >= self::T_DIRECT_INT64_MIN && $type <= self::T_DIRECT_INT64_MAX;
    }

    public static function getDirectInt64($type)
    {
        return $type - self::INT64_ZERO;
    }
}