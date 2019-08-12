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
use Breeze\Buffer;

/**
 * type byte: one byte
 * @author: zhanglei
 *  Created at: 2019-04-30
 */
class TypeByte implements Type
{
    private static $ins;

    private function __construct()
    {
    }

    public static function instance()
    {
        if (is_null(self::$ins)) {
            self::$ins = new TypeByte();
        }
        return self::$ins;
    }

    public function read(Buffer $buf, $withType = true)
    {
        if ($withType) {
            $tp = $buf->readByte();
            if ($tp != self::T_BYTE) {
                throw new BreezeException('wrong breeze byte type. type:' . $tp);
            }
        }
        return $buf->readByte();
    }

    public function write(Buffer $buf, $value, $withType = true)
    {
        if ($withType) {
            $buf->writeByte(self::T_BYTE);
        }
        $buf->writeByte($value);
    }

    public function checkType($value)
    {
        if (is_string($value) && strlen($value) == 1) {
            return true;
        } else if (is_int($value) && $value >= -128 && $value <= 127) {
            return true;
        }
        return false;
    }

    public function writeType(Buffer $buf)
    {
        $buf->writeByte(self::T_BYTE);
    }
}