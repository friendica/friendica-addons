<?php


/** seed rand */
list($gpg_usec, $gpg_sec) = explode(' ', microtime());
srand((float) $gpg_sec + ((float) $gpg_usec * 100000));

class GPG_Utility
{
    public static function starts_with($haystack, $needle)
    {
        return $needle === '' || strpos($haystack, $needle) === 0;
    }

    public static function B0($x)
    {
        return $x & 0xff;
    }

    public static function B1($x)
    {
        return ($x >> 0x8) & 0xff;
    }

    public static function B2($x)
    {
        return ($x >> 0x10) & 0xff;
    }

    public static function B3($x)
    {
        return ($x >> 0x18) & 0xff;
    }

    public static function zshift($x, $s)
    {
        $res = $x >> $s;

        $pad = 0;
        for ($i = 0; $i < 32 - $s; ++$i) {
            $pad += (1 << $i);
        }

        return $res & $pad;
    }

    public static function pack_octets($octets)
    {
        $i = 0;
        $j = 0;
        $len = count($octets);
        $b = array_fill(0, $len / 4, 0);

        if (!$octets || $len % 4) {
            return;
        }

        for ($i = 0, $j = 0; $j < $len; $j += 4) {
            $b[$i++] = $octets[$j] | ($octets[$j + 1] << 0x8) | ($octets[$j + 2] << 0x10) | ($octets[$j + 3] << 0x18);
        }

        return $b;
    }

    public static function unpack_octets($packed)
    {
        $j = 0;
        $i = 0;
        $l = count($packed);
        $r = array_fill(0, $l * 4, 0);

        for ($j = 0; $j < $l; ++$j) {
            $r[$i++] = self::B0($packed[$j]);
            $r[$i++] = self::B1($packed[$j]);
            $r[$i++] = self::B2($packed[$j]);
            $r[$i++] = self::B3($packed[$j]);
        }

        return $r;
    }

    public static function hex2bin($h)
    {
        if (strlen($h) % 2) {
            $h += '0';
        }

        $r = '';
        for ($i = 0; $i < strlen($h); $i += 2) {
            $r .= chr(intval($h[$i], 16) * 16 + intval($h[$i + 1], 16));
        }

        return $r;
    }

    public static function crc24($data)
    {
        $crc = 0xb704ce;

        for ($n = 0; $n < strlen($data); ++$n) {
            $crc ^= (ord($data[$n]) & 0xff) << 0x10;
            for ($i = 0; $i < 8; ++$i) {
                $crc <<= 1;
                if ($crc & 0x1000000) {
                    $crc ^= 0x1864cfb;
                }
            }
        }

        return
            chr(($crc >> 0x10) & 0xff).
            chr(($crc >> 0x8) & 0xff).
            chr($crc & 0xff);
    }

    public static function s_random($len, $textmode)
    {
        $r = '';
        for ($i = 0; $i < $len;) {
            $t = rand(0, 0xff);
            if ($t == 0 && $textmode) {
                continue;
            }
            ++$i;

            $r .= chr($t);
        }

        return $r;
    }

    public static function c_random()
    {
        return round(rand(0, 0xff));
    }
}
