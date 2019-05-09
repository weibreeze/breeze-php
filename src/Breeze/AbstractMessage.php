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
 * AbstractMessage contains common function of breeze message.
 * @author: zhanglei
 *  Created at: 2019-04-30
 */
abstract class AbstractMessage implements Message
{
    /**
     * @param Buffer $buf
     * @param MessageField ...$fields all MessageFields want to write to buffer.
     */
    protected function writeFields(Buffer $buf, MessageField ...$fields)
    {
        foreach ($fields as $f) {
            BreezeWriter::writeMessagField($buf, $f->getFieldDesc()->getIndex(), $f->getValue(), $f->getFieldDesc()->getType());
        }
    }
}