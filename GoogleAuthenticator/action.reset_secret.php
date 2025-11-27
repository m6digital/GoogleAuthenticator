<?php
if (!defined('CMS_VERSION')) exit;

// Admin permission required
if (!$this->CheckPermission(GoogleAuthenticator::MANAGE_PERM)) {
    echo $this->ShowErrors('You do not have permission to manage users.');
    return;
}

$userid = (int)($params['userid'] ?? 0);

if ($userid <= 0) {
    echo $this->ShowErrors('Invalid User ID.');
    return;
}

// Generate new secret
$new_secret = $this->GenerateSecret();

// Disable 2FA (so user must enroll again)
$this->DisableUser2FA($userid);

// Save new secret with enabled = 0
$this->SaveUserSecret($userid, $new_secret, false);

// Optional: log it
audit('', 'GoogleAuthenticator', "2FA secret reset for user {$userid}");

// Redirect with success message
$this->SetMessage('Secret Reset. You need to delete your Google Authenticator entry and rescan QR code');
$this->Redirect($id, 'admin_users', $returnid, ['message' => 'Secret reset successfully.']);
