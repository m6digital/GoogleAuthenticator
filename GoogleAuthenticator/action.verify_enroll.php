<?php
if (!defined('CMS_VERSION')) exit;

$userid = get_userid();
$code   = trim($params['code'] ?? '');

if (!$userid) {
    echo $this->ShowErrors('Invalid user.');
    return;
}

if ($code === '') {
    echo $this->ShowErrors('Please enter your 6-digit code.');
    return;
}

// Load secret
$secret = $this->GetUserSecret($userid);
if (!$secret) {
    echo $this->ShowErrors('No secret found. Generate one first.');
    return;
}

// Validate using preference-driven window
if (!$this->VerifyCode($secret, $code)) {
    //echo $this->ShowErrors('Invalid or expired code.');
    $this->SetError('Invalid or expired code.');
	$this->RedirectToAdminTab();
    return;
}

// Enable 2FA
$this->EnableUser2FA($userid);

// Optional: auto-generate backup codes
if ($this->GetPreference('enable_backup_codes', 0)) {
    $this->GenerateBackupCodes($userid);
}

$this->SetMessage('Enrollment Complete - Enter your 2FA Code To Continue.');
$this->RedirectToAdminTab();
return;
