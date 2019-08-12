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
 * type string. utf8 string.
 * @author: zhanglei
 *  Created at: 2019-04-30
 */
class TypeString implements Type
{
    private static $ins;

    private function __construct()
    {
    }

    public static function instance()
    {
        if (is_null(self::$ins)) {
            self::$ins = new TypeString();
        }
        return self::$ins;
    }

    public function read(Buffer $buf, $withType = true)
    {
        $tp = self::T_STRING;
        if ($withType) {
            $tp = $buf->readByte();
        }
        if (self::isDirectString($tp)) {
            return self::readStringBySize($buf, $tp);
        }
        switch ($tp) {
            case self::T_STRING:
                return TypeString::readString($buf);
            case self::T_INT32:
            case self::T_INT64:
                return (string)$buf->readZigzag();
            case self::T_INT16:
                return (string)$buf->readInt16();

            default:
                $ret = BreezeReader::readDirectBasic($buf, $tp);
                if (!is_null($ret)) {
                    return (string)$ret;
                }
                throw new BreezeException('wrong breeze string type. type:' . $tp);
        }
    }

    public function write(Buffer $buf, $value, $withType = true)
    {
        $len = strlen($value);
        if ($withType) {
            if ($len <= self::DIRECT_STRING_MAX_LENGTH) {
                $buf->writeByte($len);
                $buf->write($value);
                return;
            }
            $buf->writeByte(self::T_STRING);
        }
        $buf->writeVarInt($len);
        $buf->write($value);
    }

    public function checkType($value)
    {
        return !is_array($value) && !is_object($value);
    }

    public function writeType(Buffer $buf)
    {
        $buf->writeByte(self::T_STRING);
    }

    public static function isDirectString($type)
    {
        return $type >= self::T_DIRECT_STRING_MIN && $type <= self::T_DIRECT_STRING_MAX;
    }

    public static function readStringBySize(Buffer $buf, $size)
    {
        if ($size == 0) {
            return '';
        }
        return $buf->read($size);
    }

    // read string without type
    public static function readString(Buffer $buf)
    {
        $size = $buf->readVarInt();
        return self::readStringBySize($buf, $size);
    }
}