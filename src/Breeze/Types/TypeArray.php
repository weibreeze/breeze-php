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


namespace Breeze\Types;

use Breeze\BreezeException;
use Breeze\BreezeReader;
use Breeze\BreezeWriter;
use Breeze\Buffer;

/**
 * type array（list in java）.
 * @author: zhanglei
 *  Created at: 2019-04-30
 */
class TypeArray implements Type
{
    private static $ins;

    private function __construct()
    {
    }

    public static function instance()
    {
        if (is_null(self::$ins)) {
            self::$ins = new TypeArray();
        }
        return self::$ins;
    }

    public function read(Buffer $buf, $withType = true)
    {
        if (!$withType) {
            return self::readArray($buf);
        }
        $tp = $buf->readByte();
        switch ($tp) {
            case self::T_ARRAY:
                return self::readArray($buf);
            case self::T_PACKED_ARRAY:
                $packedArray = new TypePackedArray();
                return $packedArray->read($buf, false);
            default:
                throw new BreezeException('unsupported type by breeze array. type: ' . $tp);
        }
    }

    // read array elements
    public static function readArray(Buffer $buf)
    {
        $size = $buf->readVarInt();
        $array = array();
        if ($size > 0) {
            for ($i = 0; $i < $size; $i++) {
                $array[] = BreezeReader::readValue($buf);
            }
        }
        return $array;
    }

    public function write(Buffer $buf, $value, $withType = true)
    {
        if ($withType) {
            $buf->writeByte(self::T_ARRAY);
        }
        $size = count($value);
        $buf->writeVarInt($size);
        if ($size > 0) {
            foreach ($value as $v) {
                BreezeWriter::writeValue($buf, $v);
            }
        }
    }

    public function checkType($value)
    {
        return is_array($value) && (empty($value) || !BreezeWriter::is_assoc($value));
    }

    public function writeType(Buffer $buf)
    {
        $buf->writeByte(self::T_ARRAY);
    }
}