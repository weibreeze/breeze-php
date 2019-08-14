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
use Breeze\Message;

/**
 * type message. contains a breeze message for get default instance.
 * @author: zhanglei
 *  Created at: 2019-04-30
 */
class TypeMessage implements Type
{
    private $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function read(Buffer $buf, $withType = true)
    {
        if ($withType) {
            $tp = $buf->readByte();
            if ($tp == self::T_NULL) {
                return null;
            }
            if ($tp < self::T_MESSAGE) {
                throw new BreezeException('wrong breeze message type. type:' . $tp);
            }
            BreezeReader::readMessageNameByType($buf, $tp);
        }
        $msg = $this->message->defaultInstance();
        $msg->readFrom($buf);
        return $msg;
    }

    public function write(Buffer $buf, $value, $withType = true)
    {
        if ($withType) {
            BreezeWriter::writeMessageType($buf, $this->message->messageName());
        }
        $value->writeTo($buf);
    }

    public function checkType($value)
    {
        return ($value instanceof Message) && $value->messageName() === $this->message->messageName();
    }

    public function writeType(Buffer $buf)
    {
        BreezeWriter::writeMessageType($buf, $this->message->messageName());
    }
}