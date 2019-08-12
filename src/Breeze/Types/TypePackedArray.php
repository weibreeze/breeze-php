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
use Breeze\BreezeWriter;
use Breeze\Buffer;
use function PHPSTORM_META\elementType;

/**
 * type array（list in java）.
 * @author: zhanglei
 *  Created at: 2019-04-30
 */
class TypePackedArray implements Type
{
    private $elemType; // null means any type.

    public function __construct(Type $elemType = null)
    {
        $this->elemType = $elemType;
    }

    public function getElemType()
    {
        return $this->elemType;
    }

    public function read(Buffer $buf, $withType = true)
    {
        $tp = self::T_PACKED_ARRAY;
        if ($withType) {
            $tp = $buf->readByte();
        }
        switch ($tp) {
            case self::T_PACKED_ARRAY:
                $size = $buf->readVarInt();
                $array = array();
                if ($size > 0) {
                    if (is_null($this->elemType)) {
                        $this->elemType = BreezeReader::readType($buf);
                    } else {
                        BreezeReader::skipType($buf);
                    }
                    for ($i = 0; $i < $size; $i++) {
                        $array[] = $this->elemType->read($buf, false);
                    }
                }
                return $array;
            case self::T_ARRAY:
                return TypeArray::readArray($buf);
            default:
                throw new BreezeException('unsupported type by breeze array. type: ' . $tp);
        }
    }

    public function write(Buffer $buf, $value, $withType = true)
    {
        if ($withType) {
            $buf->writeByte(self::T_PACKED_ARRAY);
        }
        $size = count($value);
        $buf->writeVarInt($size);
        if ($size > 0) {
            if (is_null($this->elemType)) {
                throw new BreezeException('elemType must not null in TypePackedArray');
            }
            $this->elemType->writeType($buf);
            foreach ($value as $v) {
                $this->elemType->write($buf, $v, false);
            }
        }
    }

    public function checkType($value)
    {
        if (is_array($value) && (empty($value) || !BreezeWriter::is_assoc($value))) {
            foreach ($value as $v) {
                if (is_null($v)) {
                    throw new BreezeException('not support null value in breeze packed array');
                }
                if (!$this->elemType->checkType($v)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    public function writeType(Buffer $buf)
    {
        $buf->writeByte(self::T_PACKED_ARRAY);
    }
}