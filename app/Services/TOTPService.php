<?php

namespace App\Services;

class TOTPService
{
    private static $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a random 16-character Base32 secret key.
     */
    public function generateSecret(): string
    {
        $secret = '';
        for ($i = 0; $i < 16; $i++) {
            $secret .= self::$base32Chars[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Get the OTPAuth URL for QR code generation.
     */
    public function getQRCodeUrl(string $email, string $secret): string
    {
        $issuer = rawurlencode(config('app.name', 'Exam Verification System'));
        return "otpauth://totp/{$issuer}:{$email}?secret={$secret}&issuer={$issuer}";
    }

    /**
     * Verify a 6-digit TOTP code against the secret key, with clock drift tolerance.
     *
     * Returns the matched time-slice integer on success so callers can track
     * code reuse within the same 30-second window. Returns false on failure.
     *
     * @return int|false
     */
    public function verifyCode(string $secret, string $code, int $discrepancy = 1): int|false
    {
        // Clean input code
        $code = str_replace(' ', '', $code);
        if (strlen($code) !== 6 || !is_numeric($code)) {
            return false;
        }

        $currentTimeSlice = floor(time() / 30);
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $timeSlice = $currentTimeSlice + $i;
            $calculatedCode = $this->calculateCode($secret, $timeSlice);
            if (hash_equals($calculatedCode, $code)) {
                return (int) $timeSlice; // Return slice so callers can cache it
            }
        }
        return false;
    }

    /**
     * Calculate TOTP code for a specific time slice.
     */
    private function calculateCode(string $secret, int $timeSlice): string
    {
        $secretKey = $this->base32Decode($secret);
        // Pack time slice into a 64-bit binary string
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        // Generate HMAC-SHA1
        $hmac = hash_hmac('sha1', $time, $secretKey, true);
        // Extract 4-byte offset
        $offset = ord(substr($hmac, -1)) & 0x0F;
        $hashpart = substr($hmac, $offset, 4);
        // Unpack as 32-bit unsigned integer
        $value = unpack('N', $hashpart)[1] & 0x7FFFFFFF;
        // Limit to 6 digits
        $value = $value % 1000000;
        return str_pad((string)$value, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Decode a Base32 encoded string.
     */
    private function base32Decode(string $base32): string
    {
        if (empty($base32)) {
            return '';
        }
        $base32 = strtoupper($base32);
        $base32Lookup = array_flip(str_split(self::$base32Chars));
        $binaryString = '';
        foreach (str_split($base32) as $char) {
            if (isset($base32Lookup[$char])) {
                $binaryString .= str_pad(decbin($base32Lookup[$char]), 5, '0', STR_PAD_LEFT);
            }
        }
        $bytes = '';
        foreach (str_split($binaryString, 8) as $binByte) {
            if (strlen($binByte) === 8) {
                $bytes .= chr(bindec($binByte));
            }
        }
        return $bytes;
    }
}
