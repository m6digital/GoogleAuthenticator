<?php
if (!defined('CMS_VERSION')) exit;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load current CMSMS admin user
$userid = get_userid();
$user = cmsms()->GetUserOperations()->LoadUserByID($userid);

$smarty = cmsms()->GetSmarty();

// Build navigation links
$links = [];

// Enroll / Manage Personal 2FA
$links['enroll'] = $this->CreateLink(
    $id,
    'enroll',
    $returnid,
    'Set Up / Manage My 2FA'
);

// Show backup codes only if enabled
if ($this->IsUser2FAEnabled($userid)) {
    $links['backup'] = $this->CreateLink(
        $id,
        'backup_codes',
        $returnid,
        'View Backup Codes'
    );
}

// Admin-only section
if ($this->CheckPermission(GoogleAuthenticator::MANAGE_PERM)) {

    $links['admin_users'] = $this->CreateLink(
        $id,
        'admin_users',
        $returnid,
        'Manage Users'
    );

    $links['preferences'] = $this->CreateLink(
        $id,
        'admin_settings',
        $returnid,
        'Module Preferences'
    );
}

$smarty->assign('links', $links);

echo $this->ProcessTemplate('defaultadmin.tpl');
