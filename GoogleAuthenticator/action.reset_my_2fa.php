<?php
if (!isset($gCms)) exit;

$userid = get_userid(false);
if (!$userid) {
    echo "<p class='error'>".$this->Lang('error_not_logged_in')."</p>";
    return;
}

// Delete user's 2FA data completely (they'll need to re-enroll)
$this->DeleteUser2FA($userid);

$this->SetMessage($this->Lang('msg_2fa_reset'));
$this->Redirect($id, 'manage_2fa');