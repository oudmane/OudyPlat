<?php

namespace OudyPlat;

class Crypt {
    public static function genRandomPassword($length = 8, $full = true, $salt = '') {
        if(!$salt)
            if($full)
                $salt = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            else
                $salt = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $base = strlen($salt);
        $makepass = '';
        $random = self::genRandomBytes($length + 1);
        $shift = ord($random[0]);
        for($i = 1; $i <= $length; ++$i) {
            $makepass .= $salt[($shift + ord($random[$i])) % $base];
            $shift += ord($random[$i]);
        }
        return $makepass;
    }
    public static function genRandomBytes($length = 16) {
        $sslStr = '';
        if(function_exists('openssl_random_pseudo_bytes')
            && (version_compare(PHP_VERSION, '5.3.4') >= 0 || IS_WIN)) {
            $sslStr = openssl_random_pseudo_bytes($length, $strong);
            if($strong) {
                return $sslStr;
            }
        }
        $bitsPerRound = 2;
        $maxTimeMicro = 400;
        $shaHashLength = 20;
        $randomStr = '';
        $total = $length;
        $urandom = false;
        $handle = null;
        if(function_exists('stream_set_read_buffer') && @is_readable('/dev/urandom')) {
            $handle = @fopen('/dev/urandom', 'rb');
            if($handle) {
                $urandom = true;
            }
        }
        while ($length > strlen($randomStr)) {
            $bytes = ($total > $shaHashLength)? $shaHashLength : $total;
            $total -= $bytes;
            $entropy = rand() . uniqid(mt_rand(), true) . $sslStr;
            $entropy .= implode('', @fstat(fopen(__FILE__, 'r')));
            $entropy .= memory_get_usage();
            $sslStr = '';
            if($urandom) {
                stream_set_read_buffer($handle, 0);
                $entropy .= @fread($handle, $bytes);
            } else {
                $samples = 3;
                $duration = 0;
                for($pass = 0; $pass < $samples; ++$pass) {
                    $microStart = microtime(true) * 1000000;
                    $hash = sha1(mt_rand(), true);
                    for($count = 0; $count < 50; ++$count) {
                        $hash = sha1($hash, true);
                    }
                    $microEnd = microtime(true) * 1000000;
                    $entropy .= $microStart . $microEnd;
                    if($microStart > $microEnd) {
                        $microEnd += 1000000;
                    }
                    $duration += $microEnd - $microStart;
                }
                $duration = $duration / $samples;
                $rounds = (int) (($maxTimeMicro / $duration) * 50);
                $iter = $bytes * (int) ceil(8 / $bitsPerRound);
                for($pass = 0; $pass < $iter; ++$pass) {
                    $microStart = microtime(true);
                    $hash = sha1(mt_rand(), true);
                    for($count = 0; $count < $rounds; ++$count) {
                        $hash = sha1($hash, true);
                    }
                    $entropy .= $microStart . microtime(true);
                }
            }
            $randomStr .= sha1($entropy, true);
        }
        if($urandom) {
            @fclose($handle);
        }
        return substr($randomStr, 0, $length);
    }
    public static function getCryptedPassword($plaintext, $salt = '', $encryption = 'md5-hex', $show_encrypt = false) {
        $salt = self::getSalt($encryption, $salt, $plaintext);  
        switch ($encryption) {
            case 'plain':
                return $plaintext;
            case 'sha':
                $encrypted = base64_encode(mhash(MHASH_SHA1, $plaintext));
                return ($show_encrypt) ? '{SHA}' . $encrypted : $encrypted;
            case 'crypt':
            case 'crypt-des':
            case 'crypt-md5':
            case 'crypt-blowfish':
                return ($show_encrypt ? '{crypt}' : '') . crypt($plaintext, $salt);
            case 'md5-base64':
                $encrypted = base64_encode(mhash(MHASH_MD5, $plaintext));
                return ($show_encrypt) ? '{MD5}' . $encrypted : $encrypted;
            case 'ssha':
                $encrypted = base64_encode(mhash(MHASH_SHA1, $plaintext . $salt) . $salt);
                return ($show_encrypt) ? '{SSHA}' . $encrypted : $encrypted;
            case 'smd5':
                $encrypted = base64_encode(mhash(MHASH_MD5, $plaintext . $salt) . $salt);
                return ($show_encrypt) ? '{SMD5}' . $encrypted : $encrypted;
            case 'aprmd5':
                $length = strlen($plaintext);
                $context = $plaintext . '$apr1$' . $salt;
                $binary = self::_bin(md5($plaintext . $salt . $plaintext));
                for($i = $length; $i > 0; $i -= 16) {
                    $context .= substr($binary, 0, ($i > 16 ? 16 : $i));
                }
                for($i = $length; $i > 0; $i >>= 1) {
                    $context .= ($i & 1) ? chr(0) : $plaintext[0];
                }
                $binary = self::_bin(md5($context));
                for($i = 0; $i < 1000; $i++) {
                    $new = ($i & 1) ? $plaintext : substr($binary, 0, 16);
                    if($i % 3) {
                        $new .= $salt;
                    }
                    if($i % 7) {
                        $new .= $plaintext;
                    }
                    $new .= ($i & 1) ? substr($binary, 0, 16) : $plaintext;
                    $binary = self::_bin(md5($new));
                }
                $p = array();
                for($i = 0; $i < 5; $i++) {
                    $k = $i + 6;
                    $j = $i + 12;
                    if($j == 16) {
                        $j = 5;
                    }
                    $p[] = self::_toAPRMD5((ord($binary[$i]) << 16) | (ord($binary[$k]) << 8) | (ord($binary[$j])), 5);
                }
                return '$apr1$' . $salt . '$' . implode('', $p) . self::_toAPRMD5(ord($binary[11]), 3);
            case 'md5-hex':
            default:
                $encrypted = ($salt) ? md5($plaintext . $salt) : md5($plaintext);
                return ($show_encrypt) ? '{MD5}' . $encrypted : $encrypted;
        }
    }
    public static function getSalt($encryption = 'md5-hex', $seed = '', $plaintext = '') {
        switch ($encryption) {
            case 'crypt':
            case 'crypt-des':
                if($seed) {
                    return substr(preg_replace('|^{crypt}|i', '', $seed), 0, 2);
                } else {
                    return substr(md5(mt_rand()), 0, 2);
                }
            break;
            case 'crypt-md5':
                if($seed) {
                    return substr(preg_replace('|^{crypt}|i', '', $seed), 0, 12);
                } else {
                    return '$1$' . substr(md5(mt_rand()), 0, 8) . '$';
                }
            break;
            case 'crypt-blowfish':
                if($seed) {
                    return substr(preg_replace('|^{crypt}|i', '', $seed), 0, 16);
                } else {
                    return '$2$' . substr(md5(mt_rand()), 0, 12) . '$';
                }
            break;
            case 'ssha':
                if($seed) {
                    return substr(preg_replace('|^{SSHA}|', '', $seed), -20);
                } else {
                    return mhash_keygen_s2k(MHASH_SHA1, $plaintext, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
                }
            break;
            case 'smd5':
                if($seed) {
                    return substr(preg_replace('|^{SMD5}|', '', $seed), -16);
                } else {
                    return mhash_keygen_s2k(MHASH_MD5, $plaintext, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
                }
            break;
            case 'aprmd5':
                $APRMD5 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                if($seed) {
                    return substr(preg_replace('/^\$apr1\$(.{8}).*/', '\\1', $seed), 0, 8);
                } else {
                    $salt = '';
                    for($i = 0; $i < 8; $i++) {
                        $salt .= $APRMD5{rand(0, 63)};
                    }
                    return $salt;
                }
            break;
            default:
                $salt = '';
                if($seed) {
                    $salt = $seed;
                }
                return $salt;
            break;
        }
    }
    private static function _bin($hex) {
        $bin = '';
        $length = strlen($hex);
        for($i = 0; $i < $length; $i += 2) {
            $tmp = sscanf(substr($hex, $i, 2), '%x');
            $bin .= chr(array_shift($tmp));
        }
        return $bin;
    }
    protected static function _toAPRMD5($value, $count) {
        $APRMD5 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $aprmd5 = '';
        $count = abs($count);
        while (--$count) {
            $aprmd5 .= $APRMD5[$value & 0x3f];
            $value >>= 6;
        }
        return $aprmd5;
    }
}