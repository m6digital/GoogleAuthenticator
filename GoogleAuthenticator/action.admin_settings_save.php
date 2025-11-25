<?php
if (!defined('CMS_VERSION')) exit;

if (!$this->CheckPermission(GoogleAuthenticator::MANAGE_PERM)) {
    echo $this->ShowErrors('Permission denied.');
    return;
}

$this->SetPreference('require_all_users',  (int)isset($params['require_all_users']));
$this->SetPreference('grace_period_days',  (int)$params['grace_period_days']);
$this->SetPreference('enable_backup_codes',(int)isset($params['enable_backup_codes']));
$this->SetPreference('backup_code_count',  (int)$params['backup_code_count']);
$this->SetPreference('totp_discrepancy',   (int)$params['totp_discrepancy']);
$this->SetPreference('require_https',      (int)isset($params['require_https']));
$this->SetPreference('allow_root_bypass',  (int)isset($params['allow_root_bypass']));
$this->SetPreference('login_message',      trim($params['login_message']));
$this->SetPreference('app_display_name', trim($params['app_display_name']));

$this->SetMessage('Preferences saved.');
$this->RedirectToAdminTab();
