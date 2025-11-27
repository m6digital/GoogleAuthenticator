<?php
if (!defined('CMS_VERSION')) exit;

if (!$this->CheckPermission(GoogleAuthenticator::MANAGE_PERM)) {
    echo $this->ShowErrors('Permission denied.');
    return;
}

$pref = [];
$pref['app_display_name']  = $this->GetPreference('app_display_name', 7);
$pref['enable_backup_codes'] = $this->GetPreference('enable_backup_codes', 1);
$pref['totp_discrepancy']  = $this->GetPreference('totp_discrepancy', 1);
$pref['allow_root_bypass'] = $this->GetPreference('allow_root_bypass', 1);

$smarty = cmsms()->GetSmarty();
$smarty->assign('prefs', $pref);

echo $this->ProcessTemplate('admin_settings.tpl');
