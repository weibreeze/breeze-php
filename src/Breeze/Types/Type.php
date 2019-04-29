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
    const N_NULL = 0;
    const N_TRUE = 1;
    const N_FALSE = 2;
    const N_STRING = 3;
    const N_BYTE = 4;
    const N_BYTES = 5;
    const N_INT16 = 6;
    const N_INT32 = 7;
    const N_INT64 = 8;
    const N_FLOAT32 = 9;
    const N_FLOAT64 = 10;

    const N_MAP = 20;
    const N_ARRAY = 21;
    const N_MESSAGE = 22;
    const N_SCHEMA = 23;

    const N_UNKNOWN = -1; // this number is not a real breeze type number, it only used as a internal tag in php.

    /**
     * get the type number of the Type.
     * the number must in the const number list.
     * @return integer. number of the Type.
     */
    public function getTypeNum();

    /**
     * get the element type of the Type.
     * @return mixed. return false if the Type is primitive type;
     * return a Type if the Type is Array;
     * return a Type array contains key type and value type if the Type is Map;
     * return false in otherwise.
     */
    public function getElemType();

}