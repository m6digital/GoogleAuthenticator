<?php
if (!defined('CMS_VERSION')) exit;

$userid = get_userid();
$code   = trim($params['code'] ?? '');

if (!$userid) {
    echo $this->ShowErrors('Invalid user.');
    return;
}

if ($code === '') {
    $this->SetError('Please enter your 6-digit code.');
    $this->Redirect($id,'enroll',$returnid);
}

// Load secret
$secret = $this->GetUserSecret($userid);
if (!$secret) {
    $this->SetError('No secret found. Generate one first.');
    $this->Redirect($id,'enroll',$returnid);
}

// Validate using preference-driven window
if (!$this->VerifyCode($secret, $code)) {
    $this->SetError('Invalid or expired code.');
	$this->Redirect($id,'enroll',$returnid);
}

// Enable 2FA
$this->EnableUser2FA($userid);
$this->SetMessage('Enrollment Complete');


// Optional: auto-generate backup codes
if ($this->GetPreference('enable_backup_codes', 0)) {
    $this->GenerateBackupCodes($userid);
}

$this->SetMessage('Enrollment Complete');
$this->Redirect($id,'admin_users',$returnid);
