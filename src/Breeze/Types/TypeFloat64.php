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
 * type float64: always 8 bytes
 * @author: zhanglei
 *  Created at: 2019-04-30
 */
class TypeFloat64 implements Type
{
    private static $ins;

    private function __construct()
    {
    }

    public static function instance()
    {
        if (is_null(self::$ins)) {
            self::$ins = new TypeFloat64();
        }
        return self::$ins;
    }

    public function read(Buffer $buf, $withType = true)
    {
        $tp = self::T_FLOAT64;
        if ($withType) {
            $tp = $buf->readByte();
        }
        switch ($tp) {
            case self::T_FLOAT64:
                return $buf->readFloat64();
            case self::T_FLOAT32:
                return $buf->readFloat32();
            case self::T_STRING:
                return (float)TypeString::readString($buf);
            case self::T_INT32:
            case self::T_INT64:
                return $buf->readZigzag();
            default:
                $ret = BreezeReader::readDirectBasic($buf, $tp);
                if (!is_null($ret)) {
                    return (float)$ret;
                }
                throw new BreezeException('wrong breeze float64 type. type:' . $tp);
        }
    }

    public function write(Buffer $buf, $value, $withType = true)
    {
        if ($withType) {
            $buf->writeByte(self::T_FLOAT64);
        }
        $buf->writeFloat64($value);
    }

    public function checkType($value)
    {
        return is_numeric($value);
    }

    public function writeType(Buffer $buf)
    {
        $buf->writeByte(self::T_FLOAT64);
    }
}