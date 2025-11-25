<?php
if (!isset($gCms)) exit;

$userid = get_userid(false);
if (!$userid) {
    echo "<p class='error'>".$this->Lang('error_not_logged_in')."</p>";
    return;
}

// Disable user's own 2FA
$this->DisableUser2FA($userid);

$this->SetMessage($this->Lang('msg_2fa_disabled'));
$this->Redirect($id, 'manage_2fa');