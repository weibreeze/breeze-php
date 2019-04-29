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
 * breeze buffer with big_end.
 * @author: zhanglei
 *  Created at: 2019-04-30
 */
class Buffer
{
    const MAX_VARINT_BYTES = 10;

    private $buf;
    private $pos;// read position

    public function __construct($buf = '')
    {
        $this->buf = $buf;
        $this->pos = 0;
    }

    public function buffer()
    {
        return $this->buf;
    }

    public function pos()
    {
        return $this->pos;
    }

    public function len()
    {
        return strlen($this->buf);
    }

    /**
     * append length of $newBuf and all bytes $newBuf into this buffer.
     * NOTICE: it always append length even if length is zero.
     * @param Buffer $newBuf
     */
    public function appendWithLen(Buffer $newBuf)
    {
        $this->writeInt32($newBuf->len());
        if ($newBuf->len() > 0) {
            $this->buf .= $newBuf->buffer();
        }
    }

    public function write($bytes)
    {
        if (!is_null($bytes)) {
            $this->buf .= $bytes;
        }
    }

    public function writeByte($byte)
    {
        $this->write(pack('C', $byte));
    }

    public function writeInt16($int16)
    {
        $this->write(pack('n', $int16));
    }

    public function writeInt32($int32)
    {
        $this->write(pack('N', $int32));
    }

    public function writeInt64($int64)
    {
        $this->write(pack('J', $int64));
    }

    public function writeFloat32($float32)
    {
        $this->writeInt32(unpack('I', pack('f', $float32))[1]);
    }

    public function writeFloat64($float64)
    {
        $this->writeInt64(unpack('Q', pack('d', $float64))[1]);
    }

    public function writeZigzag($int)
    {
        if (PHP_INT_SIZE == 4) {
            if (bccomp($int, 0) >= 0) {
                $int = bcmul($int, 2);
            } else {
                $int = bcsub(bcmul(bcsub(0, $int), 2), 1);
            }
        } else {
            $int = ($int << 1) ^ ($int >> 63);
        }
        $this->writeVarInt($int);
    }

    public function writeVarInt($int)
    {
        $high = 0;
        $low = 0;
        if (PHP_INT_SIZE == 4) {
            self::divideInt64ToInt32($int, $high, $low);
        } else {
            $low = $int;
        }

        while (($low >= 0x80 || $low < 0) || $high != 0) {
            $this->buf .= chr($low | 0x80);
            $carry = ($high & 0x7F) << ((PHP_INT_SIZE << 3) - 7);
            $high = ($high >> 7) & ~(0x7F << ((PHP_INT_SIZE << 3) - 7));
            $low = (($low >> 7) & ~(0x7F << ((PHP_INT_SIZE << 3) - 7)) | $carry);
        }
        $this->buf .= chr($low);
    }

    /**
     * read raw bytes
     * @param $n . read length.
     * @return bool|string
     * @throws BreezeException
     */
    public function read($n)
    {
        if ($this->pos + $n > strlen($this->buf)) {
            throw new BreezeException('not enough buffer');
        }
        $v = substr($this->buf, $this->pos, $n);
        $this->pos += $n;
        return $v;
    }

    public function readByte()
    {
        return unpack('C', $this->read(1))[1];
    }

    public function readInt16()
    {
        $int16 = unpack('n', $this->read(2))[1];
        if (($int16 & 0x8000) != 0) {
            if (PHP_INT_SIZE == 4) {
                $int16 = $int16 | 0xFFFF0000;
            } else {
                $int16 = $int16 | 0xFFFFFFFFFFFF0000;
            }
        }
        return $int16;
    }

    public function readInt32()
    {
        $int32 = unpack('N', $this->read(4))[1];
        if (PHP_INT_SIZE == 8 && ($int32 & 0x80000000) != 0) {
            $int32 = $int32 | 0xFFFFFFFF00000000;
        }
        return $int32;
    }

    public function readInt64()
    {
        return unpack('J', $this->read(8))[1];
    }

    public function readFloat32()
    {
        return unpack('f', pack('I', unpack('N', $this->read(4))[1]))[1];
    }

    public function readFloat64()
    {
        return unpack('d', pack('Q', unpack('J', $this->read(8))[1]))[1];
    }

    public function readZigzag()
    {
        $int = $this->readVarInt();
        if (PHP_INT_SIZE == 4) {
            if (bcmod($int, 2) == 0) {
                return bcdiv($int, 2, 0);
            } else {
                return bcsub(0, bcdiv(bcadd($int, 1), 2, 0));
            }
        } else {
            return (($int >> 1) & 0x7FFFFFFFFFFFFFFF) ^ (-($int & 1));
        }
    }

    public function readVarInt()
    {
        $count = 0;

        if (PHP_INT_SIZE == 4) {
            $high = 0;
            $low = 0;
            $b = 0;

            do {
                if ($this->pos >= $this->len()) {
                    throw new BreezeException("read varint not enough bytes");
                }
                if ($count >= self::MAX_VARINT_BYTES) {
                    throw new BreezeException("read varint overflow");
                }
                $b = ord($this->buf[$this->pos]);
                $bits = 7 * $count;
                if ($bits >= 32) {
                    $high |= (($b & 0x7F) << ($bits - 32));
                } else if ($bits > 25) {
                    // $bits is 28 in this case.
                    $low |= (($b & 0x7F) << 28);
                    $high = ($b & 0x7F) >> 4;
                } else {
                    $low |= (($b & 0x7F) << $bits);
                }
                $count++;
                $this->pos++;
            } while ($b & 0x80);

            $result = static::combineInt32ToInt64($high, $low);
            if (bccomp($result, 0) < 0) {
                $var = bcadd($result, "18446744073709551616");
            }
            return $result;
        } else {
            $result = 0;
            $shift = 0;

            do {
                if ($count === self::MAX_VARINT_BYTES) {
                    throw new Exception("Varint overflow");
                }
                $byte = ord($this->buf[$this->pos]);
                $result |= ($byte & 0x7f) << $shift;
                $shift += 7;
                $count++;
                $this->pos++;
            } while ($byte > 0x7f);
            return $result;
        }
    }

    private static function combineInt32ToInt64($high, $low)
    {
        $neg = $high < 0;
        if ($neg) {
            $high = ~$high;
            $low = ~$low;
            $low++;
            if (!$low) {
                $high = (int)($high + 1);
            }
        }
        $result = bcadd(bcmul($high, 4294967296), $low);
        if ($low < 0) {
            $result = bcadd($result, 4294967296);
        }
        if ($neg) {
            $result = bcsub(0, $result);
        }
        return $result;
    }

    private static function divideInt64ToInt32($value, &$high, &$low)
    {
        $neg = (bccomp($value, 0) < 0);
        if ($neg) {
            $value = bcsub(0, $value);
        }

        $high = bcdiv($value, 4294967296);
        $low = bcmod($value, 4294967296);
        if (bccomp($high, 2147483647) > 0) {
            $high = (int)bcsub($high, 4294967296);
        } else {
            $high = (int)$high;
        }
        if (bccomp($low, 2147483647) > 0) {
            $low = (int)bcsub($low, 4294967296);
        } else {
            $low = (int)$low;
        }

        if ($neg) {
            $high = ~$high;
            $low = ~$low;
            $low++;
            if (!$low) {
                $high = (int)($high + 1);
            }
        }
    }
}