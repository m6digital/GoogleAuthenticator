<?php
if (!defined('CMS_VERSION')) exit;

// Must have admin permission
if (!$this->CheckPermission(GoogleAuthenticator::MANAGE_PERM)) {
    echo $this->ShowErrors('Permission denied.');
    return;
}

$userid = (int)($params['userid'] ?? 0);

if ($userid < 1) {
    echo $this->ShowErrors('Invalid user ID.');
    return;
}

// Ensure user actually exists in CMSMS
$userops = cmsms()->GetUserOperations();
$cmsuser = $userops->LoadUserByID($userid);

if (!$cmsuser) {
    echo $this->ShowErrors("User ID {$userid} does not exist.");
    return;
}

// Disable the user's 2FA settings
$this->DisableUser2FA($userid);

// Also remove any temp verification sessions if disabling self
if (isset($_SESSION[GoogleAuthenticator::SESSION_2FA_VERIFIED]) &&
    $_SESSION[GoogleAuthenticator::SESSION_2FA_VERIFIED] == $userid) {

    unset($_SESSION[GoogleAuthenticator::SESSION_2FA_VERIFIED]);
    unset($_SESSION[GoogleAuthenticator::SESSION_TEMP_USER]);
    unset($_SESSION[GoogleAuthenticator::SESSION_REDIRECT_URL]);
}

// Log the action
audit('', 'GoogleAuthenticator', "Admin disabled 2FA for user ID {$userid}");

// Redirect back to Manage Users
$this->Redirect($id, 'admin_users', $returnid, [
    'message' => '2FA disabled for user ' . $cmsuser->username
]);
