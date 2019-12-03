# Breeze-PHP
[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://github.com/weibreeze/breeze-php/blob/master/LICENSE)
[![Build Status](https://img.shields.io/travis/weibreeze/breeze-php/master.svg?label=Build)](https://travis-ci.org/weibreeze/breeze-php)
[![Latest Stable Version](https://img.shields.io/packagist/v/wei-breeze/breeze.svg?style=flat-square)](https://packagist.org/packages/wei-breeze/breeze)


# 概述
Breeze-PHP是[Breeze](https://github.com/weibreeze/breeze)序列化的php实现.

# 快速入门

## 添加composer依赖
在composer.json中添加依赖：

```json
  "require" : {
    "wei-breeze/breeze": "v0.1.0"
  }
```

## 使用breeze
1. 基础类型编解码

```php
    // 编码
    $buf = new Buffer();
    BreezeWriter::writeString($buf, 'test string');
    //解码
    $newBuf = new Buffer($buf->buffer());
    $r = TypeString::instance()->read($newBuf);
    var_dump($r);
```

2. 集合类型编解码

```php
    $buf = new Buffer();
    $tp = new TypePackedMap(TypeInt32::instance(), TypeString::instance());
    BreezeWriter::writeMap($buf, [123 => 'erw', 45 => 'wer', 657 => 'terd'], $tp->getElemType()[0], $tp->getElemType()[1]);
    //解码
    $newBuf = new Buffer($buf->buffer());
    $r = $tp->read($newBuf);
    var_dump($r);
```

3. Breeze Message编解码

```php
    $msg = new TestMsg();
    $msg->setMyInt(1234);
    $msg->setMyString('ewjo3**#J');
    
    $subMsg = new TestSubMsg();
    $subMsg->setMyString('J(*#^H');
    $subMsg->setMyInt(-345);
    $subMsg->setMyBool(true);
    $subMsg->setMyBytes(pack('N', 2435));
    $subMsg->setMyInt64(723847289347398);
    $subMsg->setMyArray([234, 5467, -678, 0]);
    $subMsg->setMyMap1(['j(*&*(' => 'fj98A)', 'J()*#' => pack('l', -4578)]);
    $subMsg->setMyMap2([234 => [-45, 0], 3465 => [0, 345]]);
    
    $msg->setMyArray([$subMsg]);
    // 编码
    $buf = new Buffer();
    $tp = new TypeMessage(new TestMsg(false));
    BreezeWriter::writeValue($buf, $msg, $tp);
    //解码
    $newBuf = new Buffer($buf->buffer());
    $r = $tp->read($newBuf);
    var_dump($r);
    
    //直接编码
    $buf = new Buffer();
    $msg->writeTo($buf);
    
    //直接解码
    $newBuf = new Buffer($buf->buffer());
    $r = new TestMsg();
    $r->readFrom($newBuf);
    var_dump($r);
```

4. 任意类型编解码(不使用BreezeType指定类型)

```php
    //编码
    $v = 1234;
    $buf = new Buffer();
    BreezeWriter::writeValue($buf, $v);
    //解码
    $newBuf = new Buffer($buf->buffer());
    $r = BreezeReader::readValue($newBuf);
    var_dump($r);
```

更多demo请参考[单元测试](https://github.com/weibreeze/breeze-php/blob/master/tests/Breeze/BreezeWriterReaderTest.php)

## 使用Breeze Schema 生成Message类
参见[breeze-generator](https://github.com/weibreeze/breeze-generator)

## Breeze协议说明

参考[Breeze协议说明](https://github.com/weibreeze/breeze/wiki/zh_protocol)
