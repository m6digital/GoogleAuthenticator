<?php
// Module information
$lang['friendlyname'] = 'Google Authenticator';
$lang['admindescription'] = 'Two-Factor Authentication using Google Authenticator (TOTP)';
$lang['ask_uninstall'] = 'Are you sure you want to uninstall this module? All 2FA data will be deleted.';

// Tabs
$lang['tab_settings'] = 'Settings';
$lang['tab_users'] = 'Users';
$lang['tab_help'] = 'Help';

// Settings
$lang['enable_2fa'] = 'Enable Two-Factor Authentication';
$lang['help_enable_2fa'] = 'When enabled, users who have enrolled in 2FA will be required to enter a verification code after logging in.';
$lang['require_all_users'] = 'Require 2FA for all users';
$lang['help_require_all_users'] = 'When enabled, all users will be required to enroll in 2FA within the grace period.';
$lang['grace_period_days'] = 'Grace period (days)';
$lang['help_grace_period_days'] = 'Number of days users have to enroll in 2FA before being locked out (0-90 days).';
$lang['save_settings'] = 'Save Settings';
$lang['msg_settings_saved'] = 'Settings saved successfully.';

// Statistics
$lang['info_statistics'] = 'Current 2FA enrollment statistics:';
$lang['total_active_users'] = 'Total active users';
$lang['enrolled_users'] = 'Users enrolled in 2FA';

// User management
$lang['info_user_management'] = 'Manage two-factor authentication for individual users.';
$lang['username'] = 'Username';
$lang['email'] = 'Email';
$lang['status_2fa'] = '2FA Status';
$lang['enrolled_date'] = 'Enrolled Date';
$lang['actions'] = 'Actions';
$lang['enabled'] = 'Enabled';
$lang['disabled'] = 'Disabled';
$lang['not_enrolled'] = 'Not enrolled';
$lang['disable'] = 'Disable';
$lang['reset'] = 'Reset';
$lang['no_users_found'] = 'No users found.';
$lang['confirm_disable_2fa'] = 'Are you sure you want to disable 2FA for this user?';
$lang['confirm_reset_2fa'] = 'Are you sure you want to reset 2FA for this user? They will need to re-enroll.';
$lang['msg_user_2fa_disabled'] = 'User 2FA has been disabled.';
$lang['msg_user_2fa_reset'] = 'User 2FA has been reset. They will need to re-enroll.';

// Enrollment
$lang['enroll_2fa_title'] = 'Enroll in Two-Factor Authentication';
$lang['already_enrolled'] = 'You are already enrolled in two-factor authentication.';
$lang['manage_2fa'] = 'Manage 2FA Settings';
$lang['enroll_instructions'] = 'Follow these steps to set up two-factor authentication:';
$lang['step_1_install_app'] = 'Step 1: Install an authenticator app';
$lang['install_app_instructions'] = 'Download and install one of these authenticator apps on your mobile device:';
$lang['step_2_scan_qr'] = 'Step 2: Scan the QR code';
$lang['scan_qr_instructions'] = 'Open your authenticator app and scan this QR code:';
$lang['manual_entry_instructions'] = 'If you cannot scan the QR code, you can manually enter this secret key:';
$lang['step_3_verify_code'] = 'Step 3: Verify your setup';
$lang['verify_code_instructions'] = 'Enter the 6-digit code shown in your authenticator app to complete setup:';
$lang['verification_code'] = 'Verification Code';
$lang['help_verification_code'] = 'Enter the 6-digit code from your authenticator app.';
$lang['verify_and_enable'] = 'Verify and Enable 2FA';
$lang['cancel'] = 'Cancel';

// Backup codes
$lang['backup_codes_title'] = 'Backup Codes';
$lang['enrollment_success'] = 'Two-factor authentication has been enabled successfully!';
$lang['backup_codes_new_info'] = 'Here are your backup codes. Each code can only be used once. Store them in a safe place.';
$lang['backup_codes_existing_info'] = 'These are your current backup codes. Each code can only be used once.';
$lang['no_backup_codes'] = 'You do not have any backup codes. Generate new codes to ensure you can access your account if you lose your device.';
$lang['generate_new_codes'] = 'Generate New Codes';
$lang['confirm_regenerate_codes'] = 'This will invalidate all existing backup codes. Are you sure?';
$lang['backup_codes_warning'] = 'Important:';
$lang['backup_codes_warning_1'] = 'Save these codes in a secure location';
$lang['backup_codes_warning_2'] = 'Each code can only be used once';
$lang['backup_codes_warning_3'] = 'You will need one of these codes if you lose access to your authenticator app';
$lang['print_codes'] = 'Print Codes';
$lang['download_codes'] = 'Download Codes';
$lang['regenerate_codes'] = 'Regenerate Codes';
$lang['continue_to_settings'] = 'Continue';
$lang['backup_codes'] = 'Backup Codes';
$lang['backup_codes_manage_info'] = 'Backup codes allow you to access your account if you lose your phone or cannot access your authenticator app.';
$lang['view_backup_codes'] = 'View Backup Codes';
$lang['regenerate_backup_codes'] = 'Regenerate Backup Codes';

// Login verification
$lang['verify_2fa_title'] = 'Two-Factor Authentication';
$lang['logged_in_as'] = 'Logged in as';
$lang['enter_verification_code'] = 'Enter Verification Code';
$lang['help_verification_code_login'] = 'Enter the 6-digit code from your authenticator app.';
$lang['verify'] = 'Verify';
$lang['use_backup_code'] = 'Use a backup code instead';
$lang['enter_backup_code'] = 'Enter Backup Code';
$lang['help_backup_code_login'] = 'Enter one of your backup codes.';
$lang['use_authenticator_app'] = 'Use authenticator app instead';

// User settings
$lang['manage_2fa_title'] = 'Manage Two-Factor Authentication';
$lang['not_enrolled_yet'] = 'You have not enrolled in two-factor authentication yet.';
$lang['enroll_now'] = 'Enroll Now';
$lang['status'] = 'Status';
$lang['2fa_enabled'] = 'Two-Factor Authentication Enabled';
$lang['reset_2fa'] = 'Reset Two-Factor Authentication';
$lang['reset_2fa_info'] = 'If you want to set up 2FA with a different device, you can reset your 2FA settings. This will require you to enroll again.';
$lang['reset_my_2fa'] = 'Reset My 2FA';
$lang['confirm_reset_my_2fa'] = 'This will delete all your 2FA settings and backup codes. You will need to enroll again. Are you sure?';
$lang['disable_2fa'] = 'Disable Two-Factor Authentication';
$lang['disable_2fa_info'] = 'If you no longer want to use two-factor authentication, you can disable it. You can re-enable it at any time.';
$lang['disable_my_2fa'] = 'Disable My 2FA';
$lang['confirm_disable_my_2fa'] = 'Are you sure you want to disable two-factor authentication?';
$lang['msg_2fa_disabled'] = 'Two-factor authentication has been disabled.';
$lang['msg_2fa_reset'] = 'Two-factor authentication has been reset.';

// Errors
$lang['error_permission_denied'] = 'Permission denied.';
$lang['error_not_logged_in'] = 'You must be logged in to access this page.';
$lang['error_user_not_found'] = 'User not found.';
$lang['error_invalid_user'] = 'Invalid user ID.';
$lang['error_invalid_code'] = 'Invalid verification code. Please try again.';
$lang['error_invalid_backup_code'] = 'Invalid backup code. Please try again.';
$lang['error_session_expired'] = 'Your session has expired. Please start the enrollment process again.';
$lang['error_2fa_not_enabled'] = 'Two-factor authentication is not enabled for your account.';

// Help
$lang['help_title'] = 'Two-Factor Authentication Help';
$lang['help_description'] = 'Two-factor authentication (2FA) adds an extra layer of security to your account by requiring a verification code in addition to your password when logging in.';
$lang['help_setup_title'] = 'Setup Instructions';
$lang['help_setup_step1'] = 'Enable 2FA in the Settings tab';
$lang['help_setup_step2'] = 'Users can enroll by visiting their profile or a dedicated enrollment page';
$lang['help_setup_step3'] = 'Users scan the QR code with their authenticator app';
$lang['help_setup_step4'] = 'Users verify their setup by entering a code from the app';
$lang['help_user_enrollment_title'] = 'User Enrollment';
$lang['help_user_enrollment_description'] = 'Each user must enroll individually. During enrollment, they will receive backup codes that can be used if they lose access to their authenticator app.';
$lang['help_backup_codes_title'] = 'Backup Codes';
$lang['help_backup_codes_description'] = 'Backup codes are single-use codes that allow users to log in if they lose access to their authenticator app. Users should store these codes securely.';
$lang['help_apps_title'] = 'Compatible Authenticator Apps';

// Parameters
$lang['help_param_action'] = 'The action to perform.';

// Events
$lang['eventdesc_LoginPost'] = 'Sent after a user logs in, used to check 2FA requirement.';
$lang['eventdesc_LogoutPost'] = 'Sent after a user logs out, used to clear 2FA session data.';