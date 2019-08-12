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
 * type boolean.
 * @author: zhanglei
 *  Created at: 2019-04-30
 */
class TypeBool implements Type
{
    private static $ins;

    private function __construct()// singleton
    {
    }

    public static function instance()
    {
        if (is_null(self::$ins)) {
            self::$ins = new TypeBool();
        }
        return self::$ins;
    }

    public function read(Buffer $buf, $withType = true)
    {
        $tp = $buf->readByte();
        if ($tp == self::T_TRUE) {
            return true;
        } elseif ($tp == self::T_FALSE) {
            return false;
        } else {
            throw new BreezeException('wrong breeze bool type. type:' . $tp);
        }
    }

    public function write(Buffer $buf, $value, $withType = true)
    {
        if ($value) {
            $buf->writeByte(self::T_TRUE);
        } else {
            $buf->writeByte(self::T_FALSE);
        }
    }

    public function checkType($value)
    {
        return is_bool($value);
    }

    public function writeType(Buffer $buf)
    {
        $buf->writeByte(self::T_TRUE);
    }

}
