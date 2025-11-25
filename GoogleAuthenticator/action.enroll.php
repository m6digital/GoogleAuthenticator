<?php
if (!defined('CMS_VERSION')) exit;

$usrops = cmsms()->GetUserOperations();
$user   = $usrops->LoadUserByID(get_userid());

if (!$user) {
    echo "No user loaded.";
    return;
}

$userid = (int)$user->id;

// ----------------------------------------------------
// If user already enrolled → redirect to AdminUser
// ----------------------------------------------------
// Already enrolled → redirect to AdminUser/defaultadmin
if ($this->IsUser2FAEnabled($userid)) {

    // Build proper admin action URL
    $url = $this->CreateLink(
        $id,
        'admin_users',
        '',
        '',
        [],
        '',
        true // URL only
    );

    // Add CMS admin token
    $url .= '&' . CMS_SECURE_PARAM_NAME . '=' . $_SESSION[CMS_USER_KEY];

    redirect($url);
    exit;
}


// ----------------------------------------------------
// Load existing secret or generate a new one
// ----------------------------------------------------
$secret = $this->GetUserSecret($userid);

if (!$secret) {
    // more secure secret (32 chars)
    $ga     = $this->getGA();
    $secret = $ga->createSecret(32);

    // save but not enabled yet
    $this->SaveUserSecret($userid, $secret, false);
}

// ----------------------------------------------------
// Build QR Code URL
// ----------------------------------------------------
$qrurl = $this->GetQRCodeUrl($user->username, $secret);

// ----------------------------------------------------
// Form URL for verification
// ----------------------------------------------------
$module_action_url = $this->CreateLink(
    $id,
    'verify_enroll',
    $returnid,
    '',
    [],
    '',
    true // return URL only
);

// ----------------------------------------------------
// Assign template variables
// ----------------------------------------------------
$smarty = cmsms()->GetSmarty();

$smarty->assign('module_action_url', $module_action_url);
$smarty->assign('secret', $secret);
$smarty->assign('qrurl', $qrurl);
$smarty->assign('userid', $userid);
$smarty->assign('id', $id);

// ----------------------------------------------------
// Render template
// ----------------------------------------------------
echo $this->ProcessTemplate('enroll.tpl');
