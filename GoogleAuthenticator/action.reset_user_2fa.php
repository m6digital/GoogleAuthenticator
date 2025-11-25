<?php
if (!isset($gCms)) exit;

// Check permission
if (!$this->CheckPermission(GoogleAuthenticator::MANAGE_PERM)) {
    echo "<p class='error'>".$this->Lang('error_permission_denied')."</p>";
    return;
}

$user_id = isset($params['user_id']) ? (int)$params['user_id'] : 0;

if (!$user_id) {
    $this->SetError($this->Lang('error_invalid_user'));
    $this->RedirectToAdminTab('users');
}

// Delete user's 2FA data
$this->DeleteUser2FA($user_id);

$this->SetMessage($this->Lang('msg_user_2fa_reset'));
$this->RedirectToAdminTab('users');