<?php
if (!defined('CMS_VERSION')) exit;

$userid = get_userid();
$smarty = cmsms()->GetSmarty();

// Fetch codes
$codes = $this->GetBackupCodes($userid);

$smarty->assign('codes', $codes);

// Add regenerate link
$regen_link = $this->CreateLink(
    $id,
    'regen_backup_codes',
    $returnid,
    'Generate New Backup Codes',
    ['userid' => $userid],
    '',
    false,
    false,
    'onclick="return confirm(\'Generate NEW backup codes? Old ones will be invalid.\')"'
);
$smarty->assign('regen_link', $regen_link);

echo $this->ProcessTemplate('backup_codes.tpl');
