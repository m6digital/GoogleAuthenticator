<?php
if (!defined('CMS_VERSION')) exit;

$db   = cmsms()->GetDb();
$dict = NewDataDictionary($db);

// Drop tables
$dict->ExecuteSQLArray(
    $dict->DropTableSQL(cms_db_prefix().'module_ga_users')
);

$dict->ExecuteSQLArray(
    $dict->DropTableSQL(cms_db_prefix().'module_ga_backup_codes')
);

// Remove permission
$this->RemovePermission(GoogleAuthenticator::MANAGE_PERM);

// Remove preferences
$this->RemovePreference('enable_2fa');
$this->RemovePreference('require_all_users');
$this->RemovePreference('grace_period_days');
