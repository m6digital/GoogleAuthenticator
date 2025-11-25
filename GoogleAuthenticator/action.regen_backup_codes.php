<?php
if (!defined('CMS_VERSION')) exit;

$userid = (int)($params['userid'] ?? 0);

// Make sure this is the logged-in user ONLY
if ($userid !== get_userid()) {
    echo $this->ShowErrors('Not authorized.');
    return;
}

// Generate new codes (10 by default)
$new_codes = $this->GenerateBackupCodes($userid);

// Optional logging
audit('', 'GoogleAuthenticator', "Backup codes regenerated for user {$userid}");

// Display on next page load
$this->Redirect($id, 'backup_codes', $returnid, ['regen' => 1]);
