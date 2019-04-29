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
 * Schema contains message name and all FieldDesc.
 * @author: zhanglei
 *  Created at: 2019-04-30
 */
class Schema
{
    private $name;
    private $alias = '';
    private $fields = array(); // index => FieldDesc

    public function __construct($name = '')
    {
        $this->name = $name;
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

    public function getFields()
    {
        return $this->fields;
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function putField(FieldDesc $field)
    {
        if (!is_null($field) && $field->getIndex() > -1 && !is_null($field->getType())) {
            $this->fields[$field->getIndex()] = $field;
        }
        return $this;
    }

    public function getField($index)
    {
        return $this->fields[$index];
    }

}