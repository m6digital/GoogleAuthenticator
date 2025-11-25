<?php
#---------------------------------------------------------------------------------------------------
# Module: GoogleAuthenticator
# Authors: [Your Name]
# Copyright: (C) 2025 [Your Name]
# Licence: GNU General Public License version 3. See http://www.gnu.org/licenses/
#---------------------------------------------------------------------------------------------------
# CMS Made Simple(TM) is (c) CMS Made Simple Foundation 2004-2025 (info@cmsmadesimple.org)
# Project's homepage is: http://www.cmsmadesimple.org
#---------------------------------------------------------------------------------------------------
# This program is free software; you can redistribute it and/or modify it under the terms of the GNU
# General Public License as published by the Free Software Foundation; either version 3 of the
# License, or (at your option) any later version.
#
# However, as a special exception to the GPL, this software is distributed
# as an addon module to CMS Made Simple. You may not use this software
# in any Non GPL version of CMS Made simple, or in any version of CMS
# Made simple that does not indicate clearly and obviously in its admin
# section that the site was built with CMS Made simple.
#
# This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
# without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# See the GNU General Public License for more details.
#---------------------------------------------------------------------------------------------------

if( !defined('CMS_VERSION') ) exit;
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once(__DIR__ . '/lib/GoogleAuthenticatorLib.php');

class GoogleAuthenticator extends CMSModule
{
    
	const MANAGE_PERM         = 'manage_GoogleAuthenticator';
    const SESSION_2FA_VERIFIED = 'ga_2fa_verified';
    const SESSION_TEMP_USER    = 'ga_temp_user';
    const SESSION_REDIRECT_URL = 'ga_redirect_url';

    public function __construct()
    {
        parent::__construct();
    }

    public function GetVersion()
    {
        return '1.0.0';
    }

    public function GetFriendlyName()
    {
        return $this->Lang('friendlyname');
    }

    public function GetAdminDescription()
    {
        return $this->Lang('admindescription');
    }

    public function IsPluginModule()
    {
        return TRUE;
    }

    public function HasAdmin()
    {
        return TRUE;
    }

    public function HandlesEvents()
    {
        return TRUE;
    }

    public function VisibleToAdminUser()
    {
        return $this->CheckPermission(self::MANAGE_PERM);
    }

    public function GetAuthor()
    {
        return '[Your Name]';
    }

    public function GetAuthorEmail()
    {
        return 'jeff@m6digital.com';
    }

    public function UninstallPreMessage()
    {
        return $this->Lang('ask_uninstall');
    }

    public function GetAdminSection()
    {
        return 'extensions';
    }

    public function MinimumCMSVersion()
    {
        return '2.2.10';
    }
	
	
    public function InitializeFrontend()
    {
        $this->RegisterModulePlugin();
        $this->RestrictUnknownParams();
        $this->SetParameterType('action', CLEAN_STRING);
        $this->SetParameterType('code', CLEAN_STRING);
    }

	public function InitializeAdmin()
	{
		\CMSMS\HookManager::add_hook('admin_add_headtext', [ $this, 'AdminAddHeadtext' ]);
	}	
	
	
	public function AdminAddHeadtext($params)
	{
		audit('', 'GoogleAuthenticator', 'admin_add_headtext fired');
		$this->Enforce2FA();
		return ''; // do not output JS/CSS — just trigger logic
	}
	
    public function GetHelp()
    {
        return @file_get_contents(__DIR__.'/README.md');
    }

    public function GetChangeLog()
    {
        return @file_get_contents(__DIR__.'/doc/changelog.inc');
    }

    public function GetEventDescription($name)
    {
        return $this->Lang('eventdesc_'.$name);
    }
	
	public function DoEvent($originator, $eventname, &$params)
	{
		audit('', 'GoogleAuthenticator', "DoEvent: originator=$originator, event=$eventname");

		if ($originator == 'Core' && $eventname == 'LoginPost') {
			audit('', 'GoogleAuthenticator', "HandleLoginPost being called");
			$this->HandleLoginPost($params);
			return;
		}

		if ($originator == 'Core' && $eventname == 'LogoutPost') {
			audit('', 'GoogleAuthenticator', "HandleLogoutPost being called");
			$this->HandleLogoutPost($params);
			return;
		}
	
	}

	
	private function Enforce2FA()
	{
		audit('', 'GoogleAuthenticator', 'Enforce2FA fired');

		$userid = get_userid();

		// Not logged in yet → skip
		if (!$userid) {
			audit('', 'GoogleAuthenticator', 'No logged-in user — skipping');
			return;
		}

		// No admin session key → skip
		if (empty($_SESSION[CMS_USER_KEY])) {
			audit('', 'GoogleAuthenticator', 'No CMS_USER_KEY in session — skipping');
			return;
		}

		audit('', 'GoogleAuthenticator', "Enforce2FA sees user_id={$userid}");

		// If user has 2FA disabled → allow
		if (!$this->IsUser2FAEnabled($userid)) {
			audit('', 'GoogleAuthenticator', "User {$userid} has 2FA disabled — allow");
			return;
		}

		// Already verified → allow
		if (!empty($_SESSION[self::SESSION_2FA_VERIFIED]) &&
			$_SESSION[self::SESSION_2FA_VERIFIED] == $userid) {

			audit('', 'GoogleAuthenticator', "User {$userid} already 2FA verified");
			return;
		}

		// Not verified → must redirect to MFA page
		$_SESSION[self::SESSION_TEMP_USER] = $userid;

		$config = cmsms()->GetConfig();
		$admin_url = $config['root_url'] . '/' . $config['admin_dir'];

		// Prevent infinite loop
		$req = $_SERVER['REQUEST_URI'] ?? '';
		if (strpos($req, 'verify_2fa') !== false ||
			strpos($req, 'process_verify_2fa') !== false) {

			audit('', 'GoogleAuthenticator', 'Already on verify page');
			return;
		}

		$url = $admin_url . '/moduleinterface.php?mact=GoogleAuthenticator,m1_,verify_2fa,0';

		audit('', 'GoogleAuthenticator', "Enforce2FA redirect to {$url}");

		redirect($url);
		exit;
	}




    /**
     * Handle post-login event to check 2FA requirement
     */
	private function HandleLoginPost($params)
	{
		$userid = $params['user']->id ?? 0;
		if (!$userid) return;

		audit('', 'GoogleAuthenticator', "HandleLoginPost: userid={$userid}");

		// If user does NOT have 2FA enabled → do nothing
		if (!$this->IsUser2FAEnabled($userid)) {
			audit('', 'GoogleAuthenticator', "User {$userid} does not have 2FA enabled");
			return;
		}

		// If already verified → nothing to do
		if (!empty($_SESSION[self::SESSION_2FA_VERIFIED]) &&
			$_SESSION[self::SESSION_2FA_VERIFIED] == $userid) {

			audit('', 'GoogleAuthenticator', "User {$userid} already verified this session");
			return;
		}

		// Just mark this user as requiring 2FA — DO NOT REDIRECT YET
		$_SESSION[self::SESSION_TEMP_USER] = $userid;

		audit('', 'GoogleAuthenticator', "Set SESSION_TEMP_USER={$userid}");
	}







    /**
     * Handle logout event
     */
	private function HandleLogoutPost($params)
	{
		unset($_SESSION[self::SESSION_2FA_VERIFIED]);
		unset($_SESSION[self::SESSION_TEMP_USER]);
		unset($_SESSION[self::SESSION_REDIRECT_URL]);
	}

    /**
     * Check if 2FA is enabled for a user
     */
    public function IsUser2FAEnabled($userid)
	{
		$db = \cms_utils::get_db();

		// Global enforcement always TRUE (but grace may still delay it)
		if ($this->GetPreference('require_all_admins', 0)) {
			return true;
		}

		// Otherwise respect stored enabled flag
		$query = "SELECT enabled FROM " . cms_db_prefix() . "module_ga_users 
				  WHERE user_id = ? AND enabled = 1";
		$row = $db->GetRow($query, [ (int)$userid ]);

		return !empty($row);
	}


    /**
     * Get user's 2FA secret
     */
    public function GetUserSecret($userid)
    {
        $db = \cms_utils::get_db();
        $query = "SELECT secret FROM " . cms_db_prefix() . "module_ga_users 
                  WHERE user_id = ?";

        return $db->GetOne($query, array((int)$userid));
    }

    /**
     * Save user's 2FA secret
     */
    public function SaveUserSecret($userid, $secret, $enabled = false)
    {
        $db = \cms_utils::get_db();

        $userid = (int)$userid;
        $enabled_flag = $enabled ? 1 : 0;

        // Check if record exists
        $query = "SELECT user_id FROM " . cms_db_prefix() . "module_ga_users WHERE user_id = ?";
        $exists = $db->GetOne($query, array($userid));

        if ($exists) {
            $query = "UPDATE " . cms_db_prefix() . "module_ga_users 
                      SET secret = ?, enabled = ?, modified_date = NOW() 
                      WHERE user_id = ?";
            $db->Execute($query, array($secret, $enabled_flag, $userid));
        } else {
            $query = "INSERT INTO " . cms_db_prefix() . "module_ga_users 
                      (user_id, secret, enabled, created_date, modified_date) 
                      VALUES (?, ?, ?, NOW(), NOW())";
            $db->Execute($query, array($userid, $secret, $enabled_flag));
        }
    }

    /**
     * Enable 2FA for a user
     */
    public function EnableUser2FA($userid)
    {
        $db = \cms_utils::get_db();
        $query = "UPDATE " . cms_db_prefix() . "module_ga_users 
                  SET enabled = 1, modified_date = NOW() 
                  WHERE user_id = ?";
        $db->Execute($query, array((int)$userid));
    }

    /**
     * Disable 2FA for a user
     */
    public function DisableUser2FA($userid)
    {
        $db = \cms_utils::get_db();
        $query = "UPDATE " . cms_db_prefix() . "module_ga_users 
                  SET enabled = 0, modified_date = NOW() 
                  WHERE user_id = ?";
        $db->Execute($query, array((int)$userid));
    }

    /**
     * Delete user's 2FA data
     */
    public function DeleteUser2FA($userid)
    {
        $db = \cms_utils::get_db();
        $query = "DELETE FROM " . cms_db_prefix() . "module_ga_users WHERE user_id = ?";
        $db->Execute($query, array((int)$userid));
    }

	 
	public function getGA()
	{
		require_once(__DIR__ . '/lib/GoogleAuthenticatorLib.php');
		return new GoogleAuthenticatorLib();
	}


    /**
     * Verify a 2FA code
     */
    public function VerifyCode($secret, $code, $discrepancy = null)
	{
		$ga = $this->getGA();

		// Use module preference if no explicit discrepancy is passed
		if ($discrepancy === null) {
			$discrepancy = (int)$this->GetPreference('totp_discrepancy', 1);
		}

		// Always return boolean
		return (bool) $ga->verifyCode($secret, $code, (int)$discrepancy);
	}

    /**
     * Generate a new secret
     */
    public function GenerateSecret($length = 16)
	{
		$ga = $this->getGA();

		try {
			return $ga->createSecret($length);
		} catch (\Throwable $e) {
			// Fallback: use a non-exception, non-crypto-safe generator
			// Still good enough for TOTP in this context.
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // base32 alphabet
			$secret = '';
			$max = strlen($chars) - 1;

			// Try to use random_int if available
			if (function_exists('random_int')) {
				for ($i = 0; $i < $length; $i++) {
					$secret .= $chars[random_int(0, $max)];
				}
			} else {
				// Last resort fallback
				for ($i = 0; $i < $length; $i++) {
					$secret .= $chars[mt_rand(0, $max)];
				}
			}

			return $secret;
		}
	}


    /**
     * Get QR code URL
     */
   public function GetQRCodeUrl($username, $secret, $title = null)
	{
		$ga = $this->getGA();

		// Pull custom name from preferences
		$app_name = trim($this->GetPreference('app_display_name', 'CMS Made Simple'));

		// Normalize empty preference
		if ($app_name === '') {
			$app_name = 'CMS Made Simple';
		}

		// Google Authenticator requires both: label and title
		$label = $app_name . ':' . $username;

		// Title is issuer — same as app name
		return $ga->getQRCodeGoogleUrl($label, $secret, $app_name);
	}




    /**
     * Get backup codes for a user
     */
    public function GetBackupCodes($userid)
    {
        $db = \cms_utils::get_db();
        $query = "SELECT code, used FROM " . cms_db_prefix() . "module_ga_backup_codes 
                  WHERE user_id = ? ORDER BY id";

        return $db->GetArray($query, array((int)$userid));
    }

    /**
     * Generate backup codes
     */
    public function GenerateBackupCodes($userid, $count = 10)
    {
        $db = \cms_utils::get_db();
        $userid = (int)$userid;
        $count  = (int)$count;

        if ($count < 1) {
            $count = 10;
        }

        // Delete existing codes
        $query = "DELETE FROM " . cms_db_prefix() . "module_ga_backup_codes WHERE user_id = ?";
        $db->Execute($query, array($userid));

        // Generate new codes
        $codes = array();
        for ($i = 0; $i < $count; $i++) {
            $code = strtoupper(substr(md5(random_bytes(16)), 0, 8));
            $codes[] = $code;

            $query = "INSERT INTO " . cms_db_prefix() . "module_ga_backup_codes 
                      (user_id, code, used, created_date) 
                      VALUES (?, ?, 0, NOW())";
            $db->Execute($query, array($userid, $code));
        }

        return $codes;
    }

    /**
     * Use a backup code
     */
    public function UseBackupCode($userid, $code)
    {
        $db = \cms_utils::get_db();
        $userid = (int)$userid;
        $code   = strtoupper(trim($code));

        if ($code === '') {
            return false;
        }

        // Check if code exists and is unused
        $query = "SELECT id FROM " . cms_db_prefix() . "module_ga_backup_codes 
                  WHERE user_id = ? AND code = ? AND used = 0";
        $id = $db->GetOne($query, array($userid, $code));

        if ($id) {
            // Mark as used
            $query = "UPDATE " . cms_db_prefix() . "module_ga_backup_codes 
                      SET used = 1, used_date = NOW() 
                      WHERE id = ?";
            $db->Execute($query, array((int)$id));
            return true;
        }
        return false;
    }
    
}
