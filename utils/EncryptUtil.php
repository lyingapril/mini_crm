<?php
/**
 * AES-128-CBC 加解密工具类（接口化设计，支持前端调试）
 * 密钥、IV 与前端完全一致，返回 JSON 格式结果，支持跨域
 */
class EncryptUtil {
    // 固定密钥（16字节，与前端一致：mini_crm_keys_25）
    private static $key = "mini_crm_keys_25";
    // 固定IV（16字节，与前端一致：1234567890abcdef）
    private static $iv = "1234567890abcdef";

    /**
     * 获取密钥（仅用于调试，可选）
     */
    public static function getKey() {
        return self::$key;
    }

    /**
     * 获取IV（仅用于调试，可选）
     */
    public static function getIv() {
        return self::$iv;
    }

    /**
     * AES-128-CBC 加密
     * @param string $data 待加密明文
     * @return string Base64 编码密文 | 空字符串（失败时）
     */
    public static function encrypt($data) {
        if (empty($data) || !is_string($data)) {
            return '';
        }

        // AES-128-CBC 加密（返回二进制数据，自动 PKCS7 填充，传入 IV）
        $encryptedBinary = openssl_encrypt(
            $data,
            "AES-128-CBC",  // CBC 模式
            self::$key,
            OPENSSL_RAW_DATA,
            self::$iv       // 传入与前端一致的 IV
        );

        // 加密失败返回空
        if ($encryptedBinary === false) {
            return '';
        }

        // Base64 编码，便于传输和前端解析
        return base64_encode($encryptedBinary);
    }

    /**
     * AES-128-CBC 解密
     * @param string $encryptedData Base64 编码密文
     * @return string 明文 | 空字符串（失败时）
     */
    public static function decrypt($encryptedData) {
        if (empty($encryptedData) || !is_string($encryptedData)) {
            return '';
        }

        // Base64 解码为二进制数据
        $encryptedBinary = base64_decode($encryptedData);
        if ($encryptedBinary === false) {
            return '';
        }

        // AES-128-CBC 解密（自动 PKCS7 去填充，传入 IV）
        $data = openssl_decrypt(
            $encryptedBinary,
            "AES-128-CBC",  // CBC 模式
            self::$key,
            OPENSSL_RAW_DATA,
            self::$iv       // 传入与前端一致的 IV
        );

        // 解密失败返回空
        return $data === false ? '' : $data;
    }
}
?>