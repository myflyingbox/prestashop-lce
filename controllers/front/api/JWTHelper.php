<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@myflyingbox.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade your module to newer
 * versions in the future.
 *
 * @author    MyFlyingBox <contact@myflyingbox.com>
 * @copyright 2016 MyFlyingBox
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Custom JWT Helper for API authentication
 * Implements a simple JWT decoder with HMAC-SHA256 signature verification
 */
class JWTHelper
{
    /**
     * Decode and verify a JWT token
     *
     * @param string $jwt The JWT token to decode
     * @param string $secret The secret key to verify the signature
     * @return object|false The decoded payload or false on error
     */
    public static function decode($jwt, $secret)
    {
        if (empty($jwt) || empty($secret)) {
            return false;
        }

        // Split the JWT into its three parts
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return false;
        }

        list($header_encoded, $payload_encoded, $signature_encoded) = $parts;

        // Decode header and payload
        $header = json_decode(self::base64UrlDecode($header_encoded));
        $payload = json_decode(self::base64UrlDecode($payload_encoded));

        if (!$header || !$payload) {
            return false;
        }

        // Verify algorithm is HS256
        if (!isset($header->alg) || $header->alg !== 'HS256') {
            return false;
        }

        // Verify signature
        $signature = self::base64UrlDecode($signature_encoded);
        $expected_signature = hash_hmac('sha256', $header_encoded . '.' . $payload_encoded, $secret, true);

        if (!hash_equals($expected_signature, $signature)) {
            return false;
        }

        // Verify expiration if present
        if (isset($payload->exp) && $payload->exp < time()) {
            return false;
        }

        // Verify not before if present
        if (isset($payload->nbf) && $payload->nbf > time()) {
            return false;
        }

        return $payload;
    }

    /**
     * Base64 URL decode
     *
     * @param string $input
     * @return string
     */
    private static function base64UrlDecode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * Base64 URL encode
     *
     * @param string $input
     * @return string
     */
    private static function base64UrlEncode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     * Encode a JWT token (for testing purposes)
     *
     * @param array $payload The payload to encode
     * @param string $secret The secret key to sign with
     * @return string The JWT token
     */
    public static function encode($payload, $secret)
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];

        $header_encoded = self::base64UrlEncode(json_encode($header));
        $payload_encoded = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', $header_encoded . '.' . $payload_encoded, $secret, true);
        $signature_encoded = self::base64UrlEncode($signature);

        return $header_encoded . '.' . $payload_encoded . '.' . $signature_encoded;
    }
}
