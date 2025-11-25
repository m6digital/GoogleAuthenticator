<?php
if (!defined('CMS_VERSION')) exit;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$config = cmsms()->GetConfig();

$userid = $_SESSION[GoogleAuthenticator::SESSION_TEMP_USER] ?? 0;
$code   = trim($params['code'] ?? '');

audit('', 'GoogleAuthenticator', "process_verify_2fa: userid={$userid}");
audit('', 'GoogleAuthenticator', "process_verify_2fa: code_entered={$code}");

if (!$userid) {
    echo $this->ShowErrors('Session expired. Please log in again.');
    return;
}

if ($code === '') {
    echo $this->ShowErrors('Please enter your verification code.');
    return;
}

// Load user secret
$secret = $this->GetUserSecret($userid);
audit('', 'GoogleAuthenticator', "process_verify_2fa: secret={$secret}");

if (!$secret) {
    echo $this->ShowErrors('No 2FA secret found for this user.');
    return;
}

$ga = $this->getGA();
$window = (int)$this->GetPreference('totp_discrepancy', 1);

// --------------------------------------------------------------
// 1) PRIMARY: Validate via 6-digit TOTP
// --------------------------------------------------------------
$is_valid_totp = false;

if (preg_match('/^[0-9]{6}$/', $code)) {
    $is_valid_totp = $ga->verifyCode($secret, $code, $window);
}

if ($is_valid_totp) {
    audit('', 'GoogleAuthenticator', "process_verify_2fa: TOTP OK for user {$userid}");

    $_SESSION[GoogleAuthenticator::SESSION_2FA_VERIFIED] = $userid;
    unset($_SESSION[GoogleAuthenticator::SESSION_TEMP_USER]);

    redirect($config['admin_url']);
    exit;
}


// --------------------------------------------------------------
// 2) SECONDARY: Backup Code
// --------------------------------------------------------------
$enable_backup = (int)$this->GetPreference('enable_backup_codes', 1);

if ($enable_backup) {

    audit('', 'GoogleAuthenticator', "process_verify_2fa: checking backup codes…");

    if ($this->UseBackupCode($userid, $code)) {

        audit('', 'GoogleAuthenticator', "process_verify_2fa: BACKUP CODE OK for user {$userid}");

        $_SESSION[GoogleAuthenticator::SESSION_2FA_VERIFIED] = $userid;
        unset($_SESSION[GoogleAuthenticator::SESSION_TEMP_USER]);

        redirect($config['admin_url']);
        exit;
    }
}


// --------------------------------------------------------------
// FAILURE: Neither TOTP nor backup code matched
// --------------------------------------------------------------
audit('', 'GoogleAuthenticator', "process_verify_2fa: INVALID CODE for user {$userid}");

echo $this->ShowErrors('Invalid verification code. Please try again.');
return;
