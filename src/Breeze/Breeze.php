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
 * Breeze contains all global function of breeze.
 * @author: zhanglei
 *  Created at: 2019-04-30
 */
class Breeze
{
    public static $IS_PACK = true;
    public static $CHECK_VALUE = true;

    private static $schemaSeeker = null;
    private static $messageInstances = array();

    /**
     * get a new breeze message instance by name.
     * @param string $name
     * @return Message. return null if not found the message.
     */
    public static function getMessage($name)
    {
        if (isset(self::$messageInstances[$name])) {
            return self::$messageInstances[$name]->defaultInstance();
        }
        return null;
    }

    /**
     * register a breeze message, thus can read such message by message name.
     * @param Message $message
     * @param bool $throwException . if true, it will throw BreezeException while message name or alias already registered.
     * @throws BreezeException
     */
    public static function registerMessage(Message $message, $throwException = true)
    {
        if (isset(self::$messageInstances[$message->messageName()])) {
            if (get_class(self::$messageInstances[$message->messageName()]) === get_class($message)) { // already registered before.
                return;
            }
            if ($throwException) {
                throw new BreezeException('message name \'' . $message->messageName() . '\' is already register in Breeze');
            }
        }
        if (isset(self::$messageInstances[$message->messageAlias()]) && $throwException) {
            throw new BreezeException('message alias \'' . $message->messageAlias() . '\' is already register in Breeze');
        }
        self::$messageInstances[$message->messageName()] = $message;
        if (!empty($message->messageAlias())) {
            self::$messageInstances[$message->messageAlias()] = $message;
        }
    }

    /**
     * set a SchemaSeeker for GenericMessage seek schema.
     * U can extend through SchemaSeeker to get schema from remote, such as configuration center
     * @param SchemaSeeker $schemaSeeker
     */
    public static function setSchemaSeeker(SchemaSeeker $schemaSeeker)
    {
        self::$schemaSeeker = $schemaSeeker;
    }

    public static function getSchemaSeeker()
    {
        if (is_null(self::$schemaSeeker)) {
            self::$schemaSeeker = new NonSeeker();
        }
        return self::$schemaSeeker;
    }
}

class NonSeeker implements SchemaSeeker
{
    /**
     * @param string $messageName
     * @return Schema|null
     */
    public function seekSchema($messageName)
    {
        return null;//do nothing
    }
}