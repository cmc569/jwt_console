<?php

namespace App\Crypto;
class Crypto {
    /* 私鑰解密 */
    public static function decode($data) {
        $rsa = new Rsa(__DIR__."/Key/private_key.pem", __DIR__."/Key/public_key.pem");
        $decryptionData = $rsa->privateDecrypt($data);
        return urldecode($decryptionData);
    }
    /* 公鑰加密 */
    public static function encode($data) {
        $rsa = new Rsa(__DIR__."/Key/private_key.pem", __DIR__."/Key/public_key.pem");
        $decryptionData = $rsa->publicEncrypt($data);
        return $decryptionData;
    }
}
