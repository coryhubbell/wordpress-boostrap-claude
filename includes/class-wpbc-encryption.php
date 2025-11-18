<?php
/**
 * WPBC Encryption Utility
 *
 * Provides encryption/decryption for sensitive data using WordPress salts
 *
 * @package    WordPress_Bootstrap_Claude
 * @subpackage Security
 * @version    3.2.0
 */

class WPBC_Encryption {

    /**
     * Encryption method
     *
     * @var string
     */
    private $cipher = 'AES-256-CBC';

    /**
     * Get encryption key
     *
     * Uses WordPress AUTH_KEY and SECURE_AUTH_KEY as encryption key
     *
     * @return string Encryption key
     */
    private function get_key() {
        // Use WordPress security constants if available
        if (defined('AUTH_KEY') && defined('SECURE_AUTH_KEY')) {
            $key = AUTH_KEY . SECURE_AUTH_KEY;
        } else {
            // Fallback to a generated key (less secure, but works standalone)
            $key = 'WPBC_DEFAULT_ENCRYPTION_KEY_CHANGE_THIS_IN_PRODUCTION';
        }

        // Hash the key to get consistent length
        return hash('sha256', $key, true);
    }

    /**
     * Encrypt data
     *
     * @param string $data Data to encrypt
     * @return string|false Encrypted data (base64 encoded) or false on failure
     */
    public function encrypt($data) {
        if (empty($data)) {
            return $data;
        }

        // Check if openssl is available
        if (!function_exists('openssl_encrypt')) {
            error_log('WPBC Encryption: OpenSSL extension not available');
            return $data; // Return unencrypted if OpenSSL not available
        }

        try {
            $key = $this->get_key();

            // Generate random IV
            $iv_length = openssl_cipher_iv_length($this->cipher);
            $iv = openssl_random_pseudo_bytes($iv_length);

            // Encrypt the data
            $encrypted = openssl_encrypt(
                $data,
                $this->cipher,
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($encrypted === false) {
                error_log('WPBC Encryption: Encryption failed');
                return false;
            }

            // Combine IV and encrypted data, then base64 encode
            $result = base64_encode($iv . $encrypted);

            return $result;

        } catch (Exception $e) {
            error_log('WPBC Encryption error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Decrypt data
     *
     * @param string $encrypted_data Encrypted data (base64 encoded)
     * @return string|false Decrypted data or false on failure
     */
    public function decrypt($encrypted_data) {
        if (empty($encrypted_data)) {
            return $encrypted_data;
        }

        // Check if openssl is available
        if (!function_exists('openssl_decrypt')) {
            error_log('WPBC Encryption: OpenSSL extension not available');
            return $encrypted_data; // Return as-is if OpenSSL not available
        }

        try {
            $key = $this->get_key();

            // Base64 decode
            $data = base64_decode($encrypted_data);
            if ($data === false) {
                // Not base64 encoded - might be unencrypted legacy data
                return $encrypted_data;
            }

            // Extract IV and encrypted data
            $iv_length = openssl_cipher_iv_length($this->cipher);
            $iv = substr($data, 0, $iv_length);
            $encrypted = substr($data, $iv_length);

            // Decrypt
            $decrypted = openssl_decrypt(
                $encrypted,
                $this->cipher,
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($decrypted === false) {
                // Decryption failed - might be unencrypted legacy data
                return $encrypted_data;
            }

            return $decrypted;

        } catch (Exception $e) {
            error_log('WPBC Decryption error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if data is encrypted
     *
     * @param string $data Data to check
     * @return bool True if data appears to be encrypted
     */
    public function is_encrypted($data) {
        if (empty($data)) {
            return false;
        }

        // Encrypted data should be base64 encoded and reasonably long
        if (strlen($data) < 32) {
            return false;
        }

        // Check if it's valid base64
        $decoded = base64_decode($data, true);
        if ($decoded === false) {
            return false;
        }

        // Check if decoded length matches expected IV + data length
        $iv_length = openssl_cipher_iv_length($this->cipher);
        return strlen($decoded) > $iv_length;
    }

    /**
     * Encrypt an array of data
     *
     * @param array $data Array to encrypt
     * @return array Encrypted array
     */
    public function encrypt_array($data) {
        if (!is_array($data)) {
            return $data;
        }

        $encrypted = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $encrypted[$key] = $this->encrypt($value);
            } else if (is_array($value)) {
                $encrypted[$key] = $this->encrypt_array($value);
            } else {
                $encrypted[$key] = $value;
            }
        }

        return $encrypted;
    }

    /**
     * Decrypt an array of data
     *
     * @param array $data Array to decrypt
     * @return array Decrypted array
     */
    public function decrypt_array($data) {
        if (!is_array($data)) {
            return $data;
        }

        $decrypted = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $decrypted[$key] = $this->decrypt($value);
            } else if (is_array($value)) {
                $decrypted[$key] = $this->decrypt_array($value);
            } else {
                $decrypted[$key] = $value;
            }
        }

        return $decrypted;
    }
}
