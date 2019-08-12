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

namespace Breeze;
/**
 *
 * @author: zhanglei
 *  Created at: 2019-08-05
 */
class Context
{
    private $messageTypeRefName = array();
    private $messageTypeRefIndex = array();
    private $messageTypeRefCount = 0;

    public function getMessageTypeName($index)
    {
        if (isset($this->messageTypeRefName[$index])) {
            return $this->messageTypeRefName[$index];
        }
        return null;
    }

    public function getMessageTypeIndex($name)
    {
        if (isset($this->messageTypeRefIndex[$name])) {
            return $this->messageTypeRefIndex[$name];
        }
        return null;
    }

    public function putMessageType($name)
    {
        if (!isset($this->messageTypeRefIndex[$name])) {
            $this->messageTypeRefCount++;
            $this->messageTypeRefName[$this->messageTypeRefCount] = $name;
            $this->messageTypeRefIndex[$name] = $this->messageTypeRefCount;
        }
    }
}