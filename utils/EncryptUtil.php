<?php
/**
 * 加密工具类（封装敏感数据加密/解密逻辑，复用性强）
 */
class EncryptUtil {
    // 加密密钥（实际项目可放在环境变量，这里简化）
    private static $key = "mini_crm_secret_key_2024";

    /**
     * AES-128-ECB加密（兼容之前的逻辑，确保数据互通）
     * @param string $data 待加密数据
     * @return string 加密后的数据
     */
    public static function encrypt($data) {
        if (empty($data)) return '';
        // openssl_encrypt需要数据长度为16的倍数，补全PKCS7Padding
        $padLength = 16 - (strlen($data) % 16);
        $data .= str_repeat(chr($padLength), $padLength);
        return openssl_encrypt($data, "AES-128-ECB", self::$key, OPENSSL_RAW_DATA);
    }

    /**
     * AES-128-ECB解密
     * @param string $encryptedData 加密后的数据
     * @return string 解密后的数据
     */
    public static function decrypt($encryptedData) {
        if (empty($encryptedData)) return '';
        $data = openssl_decrypt($encryptedData, "AES-128-ECB", self::$key, OPENSSL_RAW_DATA);
        // 去除PKCS7Padding补全
        $padLength = ord(substr($data, -1));
        return substr($data, 0, -$padLength);
    }
}