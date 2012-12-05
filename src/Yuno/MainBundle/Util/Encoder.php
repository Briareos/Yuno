<?php

namespace Yuno\MainBundle\Util;

/**
 *
 * @link http://php.net/manual/en/function.mcrypt-encrypt.php#105173
 */
class Encoder
{
    private $key;

    public function __construct($key)
    {
        $this->key = substr($key, 0, 8);
    }

    public function encrypt($str)
    {
        # Add PKCS7 padding.
        $block = mcrypt_get_block_size('des', 'ecb');
        if (($pad = $block - (strlen($str) % $block)) < $block) {
            $str .= str_repeat(chr($pad), $pad);
        }

        return bin2hex(mcrypt_encrypt(MCRYPT_DES, $this->key, $str, MCRYPT_MODE_ECB));
    }

    public function decrypt($str)
    {
        $str = mcrypt_decrypt(MCRYPT_DES, $this->key, hex2bin($str), MCRYPT_MODE_ECB);

        # Strip padding out.
        $block = mcrypt_get_block_size('des', 'ecb');
        $pad = ord($str[($len = strlen($str)) - 1]);
        if ($pad && $pad < $block && preg_match(
            '/' . chr($pad) . '{' . $pad . '}$/',
            $str
        )
        ) {
            return substr($str, 0, strlen($str) - $pad);
        }

        return $str;
    }
}