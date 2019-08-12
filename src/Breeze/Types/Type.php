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

use Breeze\Buffer;

/**
 * breeze message(field) type
 * @author: zhanglei
 *  Created at: 2019-04-30
 */
interface Type
{
    /**
     * number of Breeze Types
     */
    const T_STRING = 0x3f;
    const T_DIRECT_STRING_MIN = 0x00;
    const T_DIRECT_STRING_MAX = 0x3e;
    const T_INT32 = 0x7f;
    const T_DIRECT_INT32_MIN = 0x40;
    const T_DIRECT_INT32_MAX = 0x7e;
    const T_INT64 = 0x98;
    const T_DIRECT_INT64_MIN = 0x80;
    const T_DIRECT_INT64_MAX = 0x97;
    const T_NULL = 0x99;
    const T_TRUE = 0x9a;
    const T_FALSE = 0x9b;
    const T_BYTE = 0x9c;
    const T_BYTES = 0x9d;
    const T_INT16 = 0x9e;
    const T_FLOAT32 = 0x9f;
    const T_FLOAT64 = 0xa0;
    const T_MAP = 0xd9;
    const T_ARRAY = 0xda;
    const T_PACKED_MAP = 0xdb;
    const T_PACKED_ARRAY = 0xdc;
    const T_SCHEMA = 0xdd;
    const T_MESSAGE = 0xde;
    const T_REF_MESSAGE = 0xdf;
    const T_DIRECT_REF_MESSAGE_MAX = 0xff;

    const INT32_ZERO = 0x50;
    const INT64_ZERO = 0x88;
    const DIRECT_STRING_MAX_LENGTH = self::T_DIRECT_STRING_MAX;
    const DIRECT_INT32_MIN_VALUE = self::T_DIRECT_INT32_MIN - self::INT32_ZERO;
    const DIRECT_INT32_MAX_VALUE = self::T_DIRECT_INT32_MAX - self::INT32_ZERO;
    const DIRECT_INT64_MIN_VALUE = self::T_DIRECT_INT64_MIN - self::INT64_ZERO;
    const DIRECT_INT64_MAX_VALUE = self::T_DIRECT_INT64_MAX - self::INT64_ZERO;
    const DIRECT_REF_MESSAGE_MAX_VALUE = self::T_DIRECT_REF_MESSAGE_MAX - self::T_REF_MESSAGE;

    /**
     * read value by specific type
     * @param Buffer $buf
     * @param bool $withType . it will not read breeze type number if $withType is false.
     * @return mixed
     */
    public function read(Buffer $buf, $withType = true);

    /**
     * write value by specific type
     * @param Buffer $buf
     * @param $value
     * @param bool $withType
     */
    public function write(Buffer $buf, $value, $withType = true);

    /**
     * check if the value is valid
     * @param $value
     * @return mixed
     */
    public function checkType($value);

    /**
     * write the specific type number into the $buf
     * @param Buffer $buf
     * @return mixed
     */
    public function writeType(Buffer $buf);

}