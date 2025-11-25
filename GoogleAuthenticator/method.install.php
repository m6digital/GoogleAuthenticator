<?php
if (!defined('CMS_VERSION')) exit;

$db   = cmsms()->GetDb();
$dict = NewDataDictionary($db);

// Create users table
$fields = "
    user_id I KEY,
    secret C(255) NOTNULL,
    enabled I1 DEFAULT 0,
    first_login_date T,
    grace_expires T,
    created_date T,
    modified_date T
";
$sqlarray = $dict->CreateTableSQL(cms_db_prefix() . "module_ga_users", $fields);
$dict->ExecuteSQLArray($sqlarray);

// Create backup codes table
$fields = "
    id I KEY AUTO,
    user_id I NOTNULL,
    code C(8) NOTNULL,
    used I1 DEFAULT 0,
    created_date T,
    used_date T
";
$sqlarray = $dict->CreateTableSQL(cms_db_prefix() . "module_ga_backup_codes", $fields);
$dict->ExecuteSQLArray($sqlarray);

// Permission
$this->CreatePermission(GoogleAuthenticator::MANAGE_PERM, 'Manage Google Authenticator');

// Preferences
$this->SetPreference('enable_2fa', 1);
$this->SetPreference('app_display_name', 'CMS Made Simple');


// Register events listened to
$this->AddEventHandler('Core', 'LoginPost', false);
$this->AddEventHandler('Core', 'LogoutPost', false);
