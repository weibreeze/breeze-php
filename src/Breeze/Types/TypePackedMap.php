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
 * type map（associate array）.
 * @author: zhanglei
 *  Created at: 2019-04-30
 */
class TypePackedMap implements Type
{
    private $kType;
    private $vType;

    public function __construct(Type $keyType = null, Type $valueType = null)
    {
        $this->kType = $keyType;
        $this->vType = $valueType;
    }

    public function getElemType()
    {
        return array($this->kType, $this->vType);
    }

    public function read(Buffer $buf, $withType = true)
    {
        $tp = self::T_PACKED_MAP;
        if ($withType) {
            $tp = $buf->readByte();
        }
        switch ($tp) {
            case self::T_PACKED_MAP:
                $size = $buf->readVarInt();
                $array = array();
                if ($size > 0) {
                    if (is_null($this->kType)) {
                        $this->kType = BreezeReader::readType($buf);
                        $this->vType = BreezeReader::readType($buf);
                    } else {
                        BreezeReader::skipType($buf);
                        BreezeReader::skipType($buf);
                    }
                    for ($i = 0; $i < $size; $i++) {
                        $k = $this->kType->read($buf, false);
                        $v = $this->vType->read($buf, false);
                        $array[$k] = $v;
                    }
                }
                return $array;
            case self::T_MAP:
                return TypeMap::readMap($buf);
            default:
                throw new BreezeException('unsupported type by breeze map. type: ' . $tp);
        }
    }

    public function write(Buffer $buf, $value, $withType = true)
    {
        if ($withType) {
            $buf->writeByte(self::T_PACKED_MAP);
        }
        $size = count($value);
        $buf->writeVarInt($size);
        if ($size > 0) {
            if (is_null($this->kType)) {
                throw new BreezeException('elemType must not null in TypePackedMap');
            }
            $this->kType->writeType($buf);
            $this->vType->writeType($buf);
            foreach ($value as $k => $v) {
                $this->kType->write($buf, $k, false);
                $this->vType->write($buf, $v, false);
            }
        }
    }

    public function checkType($value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                if (is_null($k) || is_null($v)) {
                    throw new BreezeException('not support null key or null value in breeze packed map');
                }
                if (!$this->kType->checkType($k) || !$this->vType->checkType($v)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    public function writeType(Buffer $buf)
    {
        $buf->writeByte(self::T_PACKED_MAP);
    }
}