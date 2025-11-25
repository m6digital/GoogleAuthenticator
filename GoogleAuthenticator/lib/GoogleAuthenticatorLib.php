<?php
/**
 * Google Authenticator TOTP implementation for CMS Made Simple
 *
 * Originally by Michael Kliewe (PHPGangsta)
 * Modified to remove namespace and use a simple class name: GoogleAuthenticator
 *
 * BSD License
 */

class GoogleAuthenticatorLib
{
    protected $_codeLength = 6;

    /**
     * Create new secret.
     * 16 characters, randomly chosen from the allowed base32 characters.
     *
     * @param int $secretLength
     *
     * @return string
     */
    public function createSecret($secretLength = 16)
    {
        $validChars = $this->_getBase32LookupTable();

        if ($secretLength < 16 || $secretLength > 128) {
            throw new Exception('Bad secret length');
        }

        $secret = '';
        $rnd = false;

        if (function_exists('random_bytes')) {
            $rnd = random_bytes($secretLength);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $rnd = openssl_random_pseudo_bytes($secretLength, $cryptoStrong);
            if (!$cryptoStrong) $rnd = false;
        }

        if ($rnd === false) {
            throw new Exception('No secure random source available');
        }

        for ($i = 0; $i < $secretLength; ++$i) {
            $secret .= $validChars[ord($rnd[$i]) & 31];
        }

        return $secret;
    }

    /**
     * Calculate the code for a given secret and time slice.
     */
    public function getCode($secret, $timeSlice = null)
    {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }

        $secretkey = $this->_base32Decode($secret);

        $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
        $hm = hash_hmac('SHA1', $time, $secretkey, true);

        $offset = ord(substr($hm, -1)) & 0x0F;
        $hashpart = substr($hm, $offset, 4);

        $value = unpack('N', $hashpart)[1];
        $value = $value & 0x7FFFFFFF;

        $modulo = pow(10, $this->_codeLength);

        return str_pad($value % $modulo, $this->_codeLength, '0', STR_PAD_LEFT);
    }

    /**
     * Generate QR code URL (via api.qrserver.com)
     */
    public function getQRCodeGoogleUrl($name, $secret, $title = null, $params = array())
	{
		$width  = !empty($params['width'])  ? (int)$params['width']  : 200;
		$height = !empty($params['height']) ? (int)$params['height'] : 200;
		$level  = !empty($params['level'])  ? $params['level']       : 'M';

		// Build otpauth URL properly
		$otpauth = "otpauth://totp/" . rawurlencode($name) . "?secret={$secret}";

		if (!empty($title)) {
			$otpauth .= "&issuer=" . rawurlencode($title);
		}

		$encoded = rawurlencode($otpauth);

		return "https://api.qrserver.com/v1/create-qr-code/?data={$encoded}&size={$width}x{$height}&ecc={$level}";
	}



    /**
     * Verify code with optional time drift (default ±30 seconds)
     */
    public function verifyCode($secret, $code, $discrepancy = 1, $currentTimeSlice = null)
    {
        if ($currentTimeSlice === null) {
            $currentTimeSlice = floor(time() / 30);
        }

        if (strlen($code) != 6) {
            return false;
        }

        for ($i = -$discrepancy; $i <= $discrepancy; ++$i) {
            $calc = $this->getCode($secret, $currentTimeSlice + $i);
            if ($this->timingSafeEquals($calc, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set custom code length (default 6)
     */
    public function setCodeLength($length)
    {
        $this->_codeLength = $length;
        return $this;
    }

    /**
     * Base32 decode
     */
    protected function _base32Decode($secret)
    {
        if (empty($secret)) return '';

        $base32chars = $this->_getBase32LookupTable();
        $base32charsFlipped = array_flip($base32chars);

        $paddingCharCount = substr_count($secret, '=');
        if (!in_array($paddingCharCount, [0,1,3,4,6])) {
            return false;
        }

        $secret = rtrim($secret, '=');
        $secret = str_split($secret);

        $binaryString = '';

        for ($i = 0; $i < count($secret); $i += 8) {
            $x = '';
            for ($j = 0; $j < 8; ++$j) {
                $val = @ $base32charsFlipped[@$secret[$i + $j]];
                $x .= str_pad(base_convert($val, 10, 2), 5, '0', STR_PAD_LEFT);
            }

            foreach (str_split($x, 8) as $byte) {
                $binaryString .= chr(base_convert($byte, 2, 10));
            }
        }

        return $binaryString;
    }

    /**
     * Base32 character table
     */
    protected function _getBase32LookupTable()
    {
        return [
            'A','B','C','D','E','F','G','H',
            'I','J','K','L','M','N','O','P',
            'Q','R','S','T','U','V','W','X',
            'Y','Z','2','3','4','5','6','7',
            '=' // padding
        ];
    }

    /**
     * Timing-safe comparison
     */
    private function timingSafeEquals($safeString, $userString)
    {
        if (function_exists('hash_equals')) {
            return hash_equals($safeString, $userString);
        }

        if (strlen($userString) !== strlen($safeString)) {
            return false;
        }

        $result = 0;
        for ($i = 0; $i < strlen($safeString); ++$i) {
            $result |= (ord($safeString[$i]) ^ ord($userString[$i]));
        }

        return $result === 0;
    }
}
