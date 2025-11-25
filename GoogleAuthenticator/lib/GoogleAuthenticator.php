<?php
/**
 * Google Authenticator TOTP implementation (CMSMS-safe version)
 *
 * Originally by Michael Kliewe (PHPGangsta)
 * Modified for CMS Made Simple — class renamed to GoogleAuthenticatorLib
 */

class GoogleAuthenticatorLib
{
    protected $_codeLength = 6;

    public function createSecret($secretLength = 16)
    {
        $validChars = $this->_getBase32LookupTable();

        if ($secretLength < 16 || $secretLength > 128) {
            throw new Exception('Bad secret length');
        }

        $rnd = function_exists('random_bytes') ?
               random_bytes($secretLength) :
               openssl_random_pseudo_bytes($secretLength, $strong);

        if ($rnd === false) {
            throw new Exception('No secure random source available');
        }

        $secret = '';
        for ($i = 0; $i < $secretLength; $i++) {
            $secret .= $validChars[ord($rnd[$i]) & 31];
        }

        return $secret;
    }

    public function getCode($secret, $timeSlice = null)
    {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }

        $secretkey = $this->_base32Decode($secret);

        $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
        $hm   = hash_hmac('SHA1', $time, $secretkey, true);

        $offset = ord(substr($hm, -1)) & 0x0F;
        $hashpart = substr($hm, $offset, 4);

        $value = unpack("N", $hashpart)[1] & 0x7FFFFFFF;

        return str_pad($value % pow(10, $this->_codeLength), $this->_codeLength, '0', STR_PAD_LEFT);
    }

    public function getQRCodeGoogleUrl($name, $secret, $title = null, $params = array())
    {
        $width  = !empty($params['width'])  ? (int)$params['width']  : 200;
        $height = !empty($params['height']) ? (int)$params['height'] : 200;
        $level  = !empty($params['level'])  ? $params['level']       : 'M';

        $otpauth = "otpauth://totp/{$name}?secret={$secret}";

        if ($title !== null) {
            $otpauth .= '&issuer=' . urlencode($title);
        }

        return "https://api.qrserver.com/v1/create-qr-code/?data=" .
               urlencode($otpauth) .
               "&size={$width}x{$height}&ecc={$level}";
    }

    public function verifyCode($secret, $code, $discrepancy = 1, $currentTimeSlice = null)
    {
        if ($currentTimeSlice === null) {
            $currentTimeSlice = floor(time() / 30);
        }

        if (strlen($code) != 6) return false;

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            if ($this->timingSafeEquals($this->getCode($secret, $currentTimeSlice + $i), $code)) {
                return true;
            }
        }

        return false;
    }

    protected function _base32Decode($secret)
    {
        if (empty($secret)) return '';

        $alphabet = $this->_getBase32LookupTable();
        $map = array_flip($alphabet);

        $secret = rtrim($secret, '=');
        $binary = "";

        foreach (str_split($secret, 8) as $block) {
            $bits = "";
            foreach (str_split($block) as $char) {
                $bits .= str_pad(base_convert($map[$char], 10, 2), 5, "0", STR_PAD_LEFT);
            }
            foreach (str_split($bits, 8) as $byte) {
                $binary .= chr(base_convert($byte, 2, 10));
            }
        }

        return $binary;
    }

    protected function _getBase32LookupTable()
    {
        return [
            'A','B','C','D','E','F','G','H',
            'I','J','K','L','M','N','O','P',
            'Q','R','S','T','U','V','W','X',
            'Y','Z','2','3','4','5','6','7',
            '='
        ];
    }

    private function timingSafeEquals($safe, $user)
    {
        if (function_exists('hash_equals')) {
            return hash_equals($safe, $user);
        }

        if (strlen($safe) !== strlen($user)) return false;

        $result = 0;
        for ($i = 0; $i < strlen($safe); $i++) {
            $result |= (ord($safe[$i]) ^ ord($user[$i]));
        }

        return $result === 0;
    }
}
