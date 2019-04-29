<?php

use Breeze\Buffer;

class BufferTest extends PHPUnit\Framework\TestCase
{
    public function testWriteZigzag()
    {
        $array = array(
            0,
            -8,
            123,
            27348723,
            -23468,
            658989798023,
            -778329739263,
            (1 << 7) - 1,
            -1 << 7,
            (1 << 15) - 1,
            -1 << 15,
            (1 << 31) - 1,
            -1 << 31,
            (1 << 63) - 1,
            -1 << 63
        );

        foreach ($array as $v) {
            $buf = new Buffer();
            $buf->writeZigzag($v);
            $newBuf = new Buffer($buf->buffer());
            $r = $newBuf->readZigzag();
            $this->assertEquals($v, $r, 'write zigzag');
        }
    }

    public function testWriteFloat32()
    {
        $array = array(
            0,
            -8,
            (float)12,
            3.14,
            -3.14,
            2342354.576
            - 26384.273984
        );

        foreach ($array as $v) {
            $buf = new Buffer();
            $buf->writeFloat32($v);
            $this->assertEquals(4, $buf->len(), 'write length');
            $newBuf = new Buffer($buf->buffer());
            $r = $newBuf->readFloat32();
            $this->assertEquals(sprintf("%b", $v), sprintf("%b", $r), 'write float32');
        }
    }

    public function testWriteFloat64()
    {
        $array = array(
            0,
            -8,
            (float)12,
            3.14,
            -3.14,
            2342354.576
            - 26384.273984
        );

        foreach ($array as $v) {
            $buf = new Buffer();
            $buf->writeFloat64($v);
            $this->assertEquals(8, $buf->len(), 'write length');
            $newBuf = new Buffer($buf->buffer());
            $r = $newBuf->readFloat64();
            $this->assertEquals(sprintf("%b", $v), sprintf("%b", $r), 'write float64');
        }
    }

    public function testWriteInt16()
    {
        $array = array(
            0,
            -8,
            123,
            (1 << 15) - 1,
            -1 << 15
        );

        foreach ($array as $v) {
            $buf = new Buffer();
            $buf->writeInt16($v);
            $this->assertEquals(2, $buf->len(), 'write length');
            $newBuf = new Buffer($buf->buffer());
            $r = $newBuf->readInt16();
            $this->assertEquals($v, $r, 'write int16');
        }
    }

    public function testWriteInt64()
    {
        $array = array(
            0,
            -8,
            123,
            (1 << 63) - 1,
            -1 << 63
        );

        foreach ($array as $v) {
            $buf = new Buffer();
            $buf->writeInt64($v);
            $this->assertEquals(8, $buf->len(), 'write length');
            $newBuf = new Buffer($buf->buffer());
            $r = $newBuf->readInt64();
            $this->assertEquals($v, $r, 'write int64');
        }
    }

    public function testWriteByte()
    {
        $array = array(
            0,
            -8,
            123,
            (1 << 7) - 1,
            -1 << 7
        );

        foreach ($array as $v) {
            $buf = new Buffer();
            $buf->writeByte($v);
            $this->assertEquals(1, $buf->len(), 'write length');
            $newBuf = new Buffer($buf->buffer());
            $r = $newBuf->readByte(strlen($v));
            $this->assertEquals($v & 0xFF, $r & 0xFF, 'write byte');
        }
    }

    public function testWrite()
    {
        $array = array(
            'jlke^*&$#09j409Jikj4转换就第上了的Joe',
            '',
            'sd9807234kj)><?//ed.',
        );

        foreach ($array as $v) {
            $buf = new Buffer();
            $buf->write($v);
            $this->assertEquals(strlen($v), $buf->len(), 'write length');
            $newBuf = new Buffer($buf->buffer());
            $r = $newBuf->read(strlen($v));
            $this->assertEquals($v, $r, 'write string');
        }
    }

    public function testWriteInt32()
    {
        $array = array(
            0,
            -8,
            123,
            (1 << 31) - 1,
            -1 << 31
        );

        foreach ($array as $v) {
            $buf = new Buffer();
            $buf->writeInt32($v);
            $this->assertEquals(4, $buf->len(), 'write length');
            $newBuf = new Buffer($buf->buffer());
            $r = $newBuf->readInt32();
            $this->assertEquals($v, $r, 'write int32');
        }
    }

    public function testAppendWithLen()
    {
        $array = array(
            'sjlek>Ei23',
            '',
            '*&()*&U3jrj;l',
        );

        foreach ($array as $v) {
            $temp = new Buffer();
            $temp->write($v);
            $buf = new Buffer();
            $buf->appendWithLen($temp);
            $this->assertEquals(4 + strlen($v), $buf->len(), 'write length');
            $newBuf = new Buffer($buf->buffer());
            $r = $newBuf->readInt32();
            $this->assertEquals(strlen($v), $r, 'write length');
            $s = $newBuf->read($r);
            $this->assertEquals($v, $s, 'append buffer');
        }
    }
}
