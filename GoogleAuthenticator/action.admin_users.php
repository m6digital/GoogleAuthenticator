<?php
if (!defined('CMS_VERSION')) exit;

// Must have permission
if (!$this->CheckPermission(GoogleAuthenticator::MANAGE_PERM)) {
    echo $this->ShowErrors('You do not have permission to manage Google Authenticator users.');
    return;
}

$db      = cmsms()->GetDb();
$userops = cmsms()->GetUserOperations();

//GET LOGGED IN USER
$user   = $userops->LoadUserByID(get_userid());
$userid = (int)$user->id;
$smarty->assign('loggedInUser',$userid);


// Load all CMSMS admin users
$query = "SELECT user_id, username, first_name, last_name, email 
          FROM " . cms_db_prefix() . "users 
          ORDER BY username ASC";

$cms_users = $db->GetArray($query);

// Global prefs
$grace_days = (int)$this->GetPreference('grace_period_days', 0);

$rows = [];

foreach ($cms_users as $u) {
    $uid      = (int)$u['user_id'];
    $username = $u['username'];

    // *** ORIGINAL, WORKING QUERY ***
    $query2 = "SELECT enabled, secret 
               FROM " . cms_db_prefix() . "module_ga_users 
               WHERE user_id = ?";
    $ga     = $db->GetRow($query2, [$uid]);

    $enabled = $ga ? (int)$ga['enabled'] : 0;
    $secret  = $ga ? trim($ga['secret']) : '';

    // Simple placeholder for now (no per-user grace column yet)
    $grace_remaining = 'N/A';

    // Action links (unchanged)
    $actions = [];

    if ($enabled) {
        $actions[] = $this->CreateLink(
            $id, 'disable_2fa', $returnid,
            'Disable 2FA', ['userid' => $uid]
        );

        $actions[] = $this->CreateLink(
            $id, 'reset_secret', $returnid,
            'Reset Secret', ['userid' => $uid]
        );

        $actions[] = $this->CreateLink(
            $id, 'backup_codes', $returnid,
            'Backup Codes', ['userid' => $uid]
        );
    } else {
        $actions[] = $this->CreateLink(
            $id, 'enroll', $returnid,
            'Enroll User', ['userid' => $uid]
        );
    }

    $rows[] = [
        'userid'          => $uid,
        'username'        => $username,
        'fullname'        => trim($u['first_name'] . ' ' . $u['last_name']),
        'email'           => $u['email'],
        'enabled'         => $enabled,
        'grace_remaining' => $grace_remaining,
        'actions'         => implode(' | ', $actions),
    ];
}

$smarty = cmsms()->GetSmarty();
$smarty->assign('rows', $rows);

echo $this->ProcessTemplate('admin_users.tpl');
