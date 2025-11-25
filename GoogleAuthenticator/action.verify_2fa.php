<?php
if (!defined('CMS_VERSION')) exit;

$userid = $_SESSION[GoogleAuthenticator::SESSION_TEMP_USER] ?? 0;

if ($userid <= 0) {
    echo $this->ShowErrors('Invalid session. Please log in again.');
    return;
}

$userops = cmsms()->GetUserOperations();
$user = $userops->LoadUserByID($userid);

if (!$user) {
    echo $this->ShowErrors('User not found. Please log in again.');
    return;
}

$smarty = cmsms()->GetSmarty();
$smarty->assign('userid', $userid);
$smarty->assign('username', $user->username);
$smarty->assign('submit_url',
    $this->CreateLink($id, 'process_verify_2fa', $returnid, '', [], '', true)
);

echo $this->ProcessTemplate('verify_2fa.tpl');
