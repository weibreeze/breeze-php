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
 * GenericMessage. breeze message will convert to GenericMessage if its schema not found.
 * GenericMessage can get or set field by magic function __get or __set, if it can found schema through SchemaSeeker.
 * @author: zhanglei
 *  Created at: 2019-04-30
 */
class GenericMessage implements Message
{
    private $name = '';
    private $alias = '';
    private $fields = array();
    private $nameToIndex = array();
    private $schema = null;
    private $checked = false;// is check schema.

    /**
     * @param string $name the name of message
     */
    public function __construct($name = 'GenericMessage')
    {
        if (empty($name)) {
            throw new BreezeException('GenericMessage must has a name');
        }
        $this->name = $name;
    }

    /**
     * put name & index of a message field.
     * @param string $name
     * @param int $index
     * @return $this
     */
    public function putNameIndex($name, $index)
    {
        $this->nameToIndex[$name] = $index;
        return $this;
    }

    /**
     * @param array $nameToIndex set an array about name => index.
     */
    public function setNameIndex($nameToIndex)
    {
        $this->nameToIndex = $nameToIndex;
    }

    public function __get($name)
    {
        $this->checkSchema();
        if (!empty($this->nameToIndex)) {
            if (!is_null($this->nameToIndex[$name])) {
                return $this->fields[$this->nameToIndex[$name]];
            }
        }
        throw new BreezeException('can not found field by name:' . $name . ', message name:' . $this->name);
    }

    public function __set($name, $value)
    {
        $this->checkSchema();
        if (!empty($this->nameToIndex)) {
            if (!is_null($this->nameToIndex[$name])) {
                $this->fields[$this->nameToIndex[$name]] = $value;
                return;
            }
        }
        throw new BreezeException('can not found field by name:' . $name . ', message name:' . $this->name);
    }

    /**
     * put a field into message.
     * @param int $index must greater than -1
     * @param mixed $value must not null
     */
    public function putField($index, $value)
    {
        if ($index > -1 && !is_null($value)) {
            $this->fields[$index] = $value;
        }
    }

    public function getField($index)
    {
        return $this->fields[$index];
    }

    public function writeTo(Buffer $buf)
    {
        if (!empty($this->fields)) {
            $this->checkSchema();
            BreezeWriter::writeMessage($buf, $this->getName(), function (Buffer $fbuf) {
                foreach ($this->fields as $index => $value) {
                    $type = null;
                    if (!is_null($this->schema)) {
                        $f = $this->schema->getField($index);
                        if (!is_null($f)) {
                            $type = $f->getType();
                        }
                    }
                    BreezeWriter::writeMessagField($fbuf, $index, $value, $type);
                }
            });
        }
    }

    public function readFrom(Buffer $buf)
    {
        $this->checkSchema();
        BreezeReader::readMessage($buf, function (Buffer $fbuf, $index) {
            $type = null;
            if (!is_null($this->schema)) {
                $f = $this->schema->getField($index);
                if (!is_null($f)) {
                    $type = $f->getType();
                }
            }
            $this->fields[$index] = BreezeReader::readValue($fbuf, $type);
        });
    }

    private function checkSchema()
    {
        if (!$this->checked && is_null($this->schema)) {
            $this->schema = Breeze::getSchemaSeeker()->seekSchema($this->name);
            if (!is_null($this->schema)) {
                foreach ($this->schema->getFields() as $field) {
                    $this->nameToIndex[$field->getName()] = $field->getIndex();
                }
            }
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function setSchema(Schema $schema)
    {
        $this->schema = $schema;
    }

    public function defaultInstance()
    {
        return new GenericMessage();
    }
}