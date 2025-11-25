<?php
if (!isset($gCms)) exit;

$userid = get_userid(false);
if (!$userid) {
    echo "<p class='error'>".$this->Lang('error_not_logged_in')."</p>";
    return;
}

// Handle cancel
if (isset($params['cancel'])) {
    unset($_SESSION['ga_temp_secret']);
    $this->Redirect($id, 'enroll_2fa');
}

// Process enrollment
if (isset($params['submit'])) {
    if (!isset($_SESSION['ga_temp_secret'])) {
        echo "<p class='error'>".$this->Lang('error_session_expired')."</p>";
        return;
    }
    
    $secret = $_SESSION['ga_temp_secret'];
    $code = isset($params['verification_code']) ? trim($params['verification_code']) : '';
    
    // Verify the code
    if ($this->VerifyCode($secret, $code, 2)) {
        // Save the secret and enable 2FA
        $this->SaveUserSecret($userid, $secret, true);
        
        // Generate backup codes
        $backup_codes = $this->GenerateBackupCodes($userid, 10);
        
        // Clear temporary secret
        unset($_SESSION['ga_temp_secret']);
        
        // Store backup codes in session to display
        $_SESSION['ga_backup_codes'] = $backup_codes;
        
        // Redirect to show backup codes
        $this->Redirect($id, 'show_backup_codes');
    } else {
        // Verification failed
        $this->SetError($this->Lang('error_invalid_code'));
        $this->Redirect($id, 'enroll_2fa');
    }
}