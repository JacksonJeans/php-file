<?php

namespace JacksonJeans;

/**
 * AESCryptoStreamFactory - Klasse
 * Vorrausetzung:
 * - openssl, sodium
 *  https://www.php.net/manual/de/sodium.installation.php
 *  https://www.php.net/manual/de/openssl.installation.php
 * - php 7.1
 *  migrierung auf 5.3 möglich - entferne Type Hints
 * 
 * @category    Class
 * @package     JacksonJeans
 * @author      Julian Tietz <julian.tietz@gidutex.de>
 * @license     Julian Tietz <julian.tietz@gidutex.de>
 * @version     3.1
 */

class AESCryptoStreamFactory
{
    /**
     * default: 8 fpr PKSC5! 
     * - The block size is a property of the used cipher algorithm. 
     * For AES it is always 16 bytes. So strictly speaking, 
     * PKCS5Padding cannot be used with AES since it is defined only for a block size of 8 bytes.
     * 
     * The only difference between these padding schemes is that PKCS7Padding has the block size as a parameter,
     * while for PKCS5Padding it is fixed at 8 bytes. When the Block size is 8 bytes they do exactly the same.
     * 
     * - Eigene Implementierung durch getPaddedText();.
     * 
     * - https://crypto.stackexchange.com/questions/43489/how-does-aes-ctr-pkcs5padding-works-when-the-bits-to-encrypt-is-more-than-8-bits
     * - https://stackoverflow.com/questions/20770072/aes-cbc-pkcs5padding-vs-aes-cbc-pkcs7padding-with-256-key-size-performance-java/20770158
     */
    public const BLOCK_SIZE = 8;

    /**
     * default: 16
     * @var int IV_LENGTH
     * - Vektor mit Zufallsdaten
     */
    public const IV_LENGTH = 16;

    /**
     * default: AES256
     * @var string CIPHER
     * - Verschlüsselung
     */
    public const CIPHER = 'AES256';

    public static function generateIv(bool $allowLessSecure = false): string
    {
        $success = false;
        $random = openssl_random_pseudo_bytes(openssl_cipher_iv_length(static::CIPHER));
        if (!$success) {
            if (function_exists('sodium_randombytes_random16')) {
                $random = sodium_randombytes_random16();
            } else {
                try {
                    $random = random_bytes(static::IV_LENGTH);
                } catch (\Exception $e) {
                    if ($allowLessSecure) {
                        $permitted_chars = implode(
                            '',
                            array_merge(
                                range('A', 'z'),
                                range(0, 9),
                                str_split('~!@#$%&*()-=+{};:"<>,.?/\'')
                            )
                        );
                        $random = '';
                        for ($i = 0; $i < static::IV_LENGTH; $i++) {
                            $random .= $permitted_chars[mt_rand(0, (static::IV_LENGTH) - 1)];
                        }
                    } else {
                        throw new \RuntimeException('Kann keinen Initialisierungsvektor erzeugen (IV)');
                    }
                }
            }
        }
        return $random;
    }

    /**
     * Erhalte aufegfüllten Text
     * @param string $plainText
     *  - Plain Text
     * @return string $plainText 
     *  - aufgefüllter Plain Text
     */
    protected static function getPaddedText(string $plainText): string
    {
        $stringLength = strlen($plainText);
        if ($stringLength % static::BLOCK_SIZE) {
            $plainText = str_pad($plainText, $stringLength + static::BLOCK_SIZE - $stringLength % static::BLOCK_SIZE, "\0");
        }
        return $plainText;
    }

    /**
     * Erhalte verschlüsselten String
     * @param string $plainText [required]
     * - unverschlüsselter Text
     * @param string $key [required]
     * - Schlüssel zum verschlüsseln
     * @param string $iv [required]
     * - Der Salt Wert für den Block zum verschlüsseln.
     * @return string 
     * - verschlüsselten String
     */
    public static function encrypt(string $plainText, string $key, string $iv): string
    {
        $plainText = static::getPaddedText($plainText);
        return openssl_encrypt($plainText, static::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
    }

    /**
     * Erhalte entschlüsselten String
     * @param string $encryptedText
     * - verschlüsselter Text
     * @param string $key 
     * - Schlüssel zum entschlüsseln
     * @param string $iv 
     * - Der Salt Wert für den Block zum entschlüsseln.
     * @return string 
     * - entschlüsselten String
     */
    public static function decrypt(string $encryptedText, string $key, string $iv): string
    {
        return openssl_decrypt($encryptedText, static::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
    }
}
