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

/**
 * type array（list in java）.
 * @author: zhanglei
 *  Created at: 2019-04-30
 */
class TypeArray implements Type
{
    private $elemType; // null means any type.

    public function __construct(Type $elemType = null)
    {
        $this->elemType = $elemType;
    }

    public function getTypeNum()
    {
        return Type::N_ARRAY;
    }

    public function getElemType()
    {
        return $this->elemType;
    }
}