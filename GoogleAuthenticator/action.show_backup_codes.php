<?php
if (!isset($gCms)) exit;

$userid = get_userid(false);
if (!$userid) {
    echo "<p class='error'>".$this->Lang('error_not_logged_in')."</p>";
    return;
}

// Get backup codes from session or database
$backup_codes = array();
if (isset($_SESSION['ga_backup_codes'])) {
    $backup_codes = $_SESSION['ga_backup_codes'];
    $is_new = true;
    unset($_SESSION['ga_backup_codes']);
} else {
    // Load from database
    $codes_data = $this->GetBackupCodes($userid);
    foreach ($codes_data as $code_data) {
        if (!$code_data['used']) {
            $backup_codes[] = $code_data['code'];
        }
    }
    $is_new = false;
}

?>
<h3><?php echo $this->Lang('backup_codes_title'); ?></h3>

<?php if ($is_new): ?>
    <div class="information">
        <p><strong><?php echo $this->Lang('enrollment_success'); ?></strong></p>
        <p><?php echo $this->Lang('backup_codes_new_info'); ?></p>
    </div>
<?php else: ?>
    <div class="pageoverflow">
        <p><?php echo $this->Lang('backup_codes_existing_info'); ?></p>
    </div>
<?php endif; ?>

<?php if (empty($backup_codes)): ?>
    <div class="warning">
        <p><?php echo $this->Lang('no_backup_codes'); ?></p>
        <p>
            <a href="<?php echo $this->CreateLink('m1_', 'regenerate_backup_codes'); ?>" 
               class="pagebutton"
               onclick="return confirm('<?php echo $this->Lang('confirm_regenerate_codes'); ?>');">
                <?php echo $this->Lang('generate_new_codes'); ?>
            </a>
        </p>
    </div>
<?php else: ?>
    <div class="pageoverflow">
        <div style="background: #f5f5f5; padding: 20px; margin: 20px 0; border-radius: 5px; border: 2px solid #ddd;">
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; max-width: 400px; margin: 0 auto;">
                <?php foreach ($backup_codes as $code): ?>
                    <div style="background: white; padding: 10px; text-align: center; border: 1px solid #ccc; border-radius: 3px;">
                        <strong style="font-family: monospace; font-size: 14px; letter-spacing: 1px;">
                            <?php echo htmlspecialchars($code); ?>
                        </strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="warning" style="margin: 20px 0;">
            <p><strong><?php echo $this->Lang('backup_codes_warning'); ?></strong></p>
            <ul>
                <li><?php echo $this->Lang('backup_codes_warning_1'); ?></li>
                <li><?php echo $this->Lang('backup_codes_warning_2'); ?></li>
                <li><?php echo $this->Lang('backup_codes_warning_3'); ?></li>
            </ul>
        </div>
        
        <div style="text-align: center; margin: 20px 0;">
            <button onclick="window.print();" class="pagebutton">
                <?php echo $this->Lang('print_codes'); ?>
            </button>
            <button onclick="downloadCodes();" class="pagebutton">
                <?php echo $this->Lang('download_codes'); ?>
            </button>
            <?php if (!$is_new): ?>
                <a href="<?php echo $this->CreateLink('m1_', 'regenerate_backup_codes'); ?>" 
                   class="pagebutton"
                   onclick="return confirm('<?php echo $this->Lang('confirm_regenerate_codes'); ?>');">
                    <?php echo $this->Lang('regenerate_codes'); ?>
                </a>
            <?php endif; ?>
        </div>
        
        <?php if ($is_new): ?>
            <div style="text-align: center; margin: 20px 0;">
                <a href="<?php echo $this->CreateLink('m1_', 'manage_2fa'); ?>" class="pagebutton">
                    <?php echo $this->Lang('continue_to_settings'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
    function downloadCodes() {
        var codes = <?php echo json_encode($backup_codes); ?>;
        var text = "Google Authenticator Backup Codes\n";
        text += "Generated: " + new Date().toLocaleString() + "\n";
        text += "=" .repeat(50) + "\n\n";
        text += "IMPORTANT: Store these codes in a safe place.\n";
        text += "Each code can only be used once.\n\n";
        for (var i = 0; i < codes.length; i++) {
            text += (i + 1) + ". " + codes[i] + "\n";
        }
        
        var element = document.createElement('a');
        element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
        element.setAttribute('download', 'backup-codes-' + Date.now() + '.txt');
        element.style.display = 'none';
        document.body.appendChild(element);
        element.click();
        document.body.removeChild(element);
    }
    </script>
    
    <style media="print">
        .pagebutton, button, a.pagebutton { display: none !important; }
        .warning { page-break-before: always; }
    </style>
<?php endif; ?>