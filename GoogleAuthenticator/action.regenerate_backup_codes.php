<?php
if (!isset($gCms)) exit;

$userid = get_userid(false);
if (!$userid) {
    echo "<p class='error'>".$this->Lang('error_not_logged_in')."</p>";
    return;
}

// Verify user has 2FA enabled
if (!$this->IsUser2FAEnabled($userid)) {
    echo "<p class='error'>".$this->Lang('error_2fa_not_enabled')."</p>";
    return;
}

// Generate new backup codes
$backup_codes = $this->GenerateBackupCodes($userid, 10);

// Store in session to display
$_SESSION['ga_backup_codes'] = $backup_codes;

// Redirect to show codes
$this->Redirect($id, 'show_backup_codes');