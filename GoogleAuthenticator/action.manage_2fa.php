<?php
if (!isset($gCms)) exit;

$userid = get_userid(false);
if (!$userid) {
    echo "<p class='error'>".$this->Lang('error_not_logged_in')."</p>";
    return;
}

$user = \cms_utils::get_user_by_id($userid);
$is_enrolled = $this->IsUser2FAEnabled($userid);

?>
<h3><?php echo $this->Lang('manage_2fa_title'); ?></h3>

<?php if (!$is_enrolled): ?>
    <div class="information">
        <p><?php echo $this->Lang('not_enrolled_yet'); ?></p>
        <p>
            <a href="<?php echo $this->CreateLink('m1_', 'enroll_2fa'); ?>" class="pagebutton">
                <?php echo $this->Lang('enroll_now'); ?>
            </a>
        </p>
    </div>
<?php else: ?>
    <div class="pageoverflow">
        <p class="information">
            <strong><?php echo $this->Lang('status'); ?>:</strong> 
            <span style="color: green;">✓ <?php echo $this->Lang('2fa_enabled'); ?></span>
        </p>
    </div>
    
    <div class="pageoverflow">
        <h4><?php echo $this->Lang('backup_codes'); ?></h4>
        <p><?php echo $this->Lang('backup_codes_manage_info'); ?></p>
        <p>
            <a href="<?php echo $this->CreateLink('m1_', 'show_backup_codes'); ?>" class="pagebutton">
                <?php echo $this->Lang('view_backup_codes'); ?>
            </a>
            <a href="<?php echo $this->CreateLink('m1_', 'regenerate_backup_codes'); ?>" 
               class="pagebutton"
               onclick="return confirm('<?php echo $this->Lang('confirm_regenerate_codes'); ?>');">
                <?php echo $this->Lang('regenerate_backup_codes'); ?>
            </a>
        </p>
    </div>
    
    <div class="pageoverflow">
        <h4><?php echo $this->Lang('reset_2fa'); ?></h4>
        <p><?php echo $this->Lang('reset_2fa_info'); ?></p>
        <p>
            <a href="<?php echo $this->CreateLink('m1_', 'reset_my_2fa'); ?>" 
               class="pagebutton"
               onclick="return confirm('<?php echo $this->Lang('confirm_reset_my_2fa'); ?>');"
               style="background-color: #ff9800;">
                <?php echo $this->Lang('reset_my_2fa'); ?>
            </a>
        </p>
    </div>
    
    <div class="pageoverflow">
        <h4><?php echo $this->Lang('disable_2fa'); ?></h4>
        <p><?php echo $this->Lang('disable_2fa_info'); ?></p>
        <p>
            <a href="<?php echo $this->CreateLink('m1_', 'disable_my_2fa'); ?>" 
               class="pagebutton"
               onclick="return confirm('<?php echo $this->Lang('confirm_disable_my_2fa'); ?>');"
               style="background-color: #f44336;">
                <?php echo $this->Lang('disable_my_2fa'); ?>
            </a>
        </p>
    </div>
<?php endif; ?>