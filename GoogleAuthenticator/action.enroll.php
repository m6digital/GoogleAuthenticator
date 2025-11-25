<?php
if (!defined('CMS_VERSION')) exit;

$user = cmsms()->GetUserOperations()->LoadUserByID(get_userid());
if (!$user) {
    echo "No user loaded";
    return;
}







$userid = (int)$user->id;

// Load existing secret (if any)
$secret = $this->GetUserSecret($userid);

// If no secret, generate one

if (!$secret) {
    $ga = $this->getGA(); // GoogleAuthenticatorLib()
    $secret = $ga->createSecret();
    $this->SaveUserSecret($userid, $secret, false);
}


// Build QR code URL
$qrurl = $this->GetQRCodeUrl($user->username, $secret);

$module_action_url = $this->CreateLink(
    $id,
    'verify_enroll',
    $returnid,
    '',
    [],
    '',
    true
);


$smarty = cmsms()->GetSmarty();

$smarty->assign('module_action_url', $module_action_url);
$smarty->assign('secret', $secret);
$smarty->assign('qrurl', $qrurl);
$smarty->assign('userid', $userid);
$smarty->assign('id', $id);

echo $this->ProcessTemplate('enroll.tpl');
