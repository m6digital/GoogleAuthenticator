<?php
if (!isset($gCms)) exit;

// Check permission
if (!$this->CheckPermission(GoogleAuthenticator::MANAGE_PERM)) {
    echo "<p class='error'>".$this->Lang('error_permission_denied')."</p>";
    return;
}

// Process form submission
if (isset($params['submit'])) {
    $enable_2fa = isset($params['enable_2fa']) ? 1 : 0;
    $require_all_users = isset($params['require_all_users']) ? 1 : 0;
    $grace_period_days = isset($params['grace_period_days']) ? (int)$params['grace_period_days'] : 7;
    
    // Validate
    if ($grace_period_days < 0 || $grace_period_days > 90) {
        $grace_period_days = 7;
    }
    
    // Save preferences
    $this->SetPreference('enable_2fa', $enable_2fa);
    $this->SetPreference('require_all_users', $require_all_users);
    $this->SetPreference('grace_period_days', $grace_period_days);
    
    // Redirect with success message
    $this->SetMessage($this->Lang('msg_settings_saved'));
    $this->Redirect($id, 'defaultadmin', '', array('active_tab' => 'settings'));
}