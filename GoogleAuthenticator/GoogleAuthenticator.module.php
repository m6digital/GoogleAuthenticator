<?php
#---------------------------------------------------------------------------------------------------
# Module: GoogleAuthenticator
# Authors: Jeff Minus
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

    public function GetVersion() { return '1.0.0'; }
    public function GetFriendlyName(){ return $this->Lang('friendlyname'); }
    public function GetAdminDescription() { return $this->Lang('admindescription'); }
    public function IsPluginModule() { return TRUE; }
    public function HasAdmin() { return TRUE; }
    public function HandlesEvents() { return TRUE; }
    public function VisibleToAdminUser() { return $this->CheckPermission(self::MANAGE_PERM);}
    public function GetAuthor(){ return 'Jeff Minus'; }
    public function GetAuthorEmail(){ return 'jeff@m6digital.com'; }
    public function UninstallPreMessage(){return $this->Lang('ask_uninstall'); }
    public function GetAdminSection(){ return 'extensions';}
    public function MinimumCMSVersion(){ return '2.2.10'; }
    public function GetHelp(){ return @file_get_contents(__DIR__.'/README.md'); }
    public function GetChangeLog(){return @file_get_contents(__DIR__.'/changelog.inc');}
    public function GetEventDescription($name){ return $this->Lang('eventdesc_'.$name);}
	public function InitializeAdmin(){ \CMSMS\HookManager::add_hook('admin_add_headtext', [ $this, 'AdminAddHeadtext' ]);}	
		
    public function InitializeFrontend()
    {
        $this->RegisterModulePlugin();
        $this->RestrictUnknownParams();
        $this->SetParameterType('action', CLEAN_STRING);
        $this->SetParameterType('code', CLEAN_STRING);
    }
		
	public function AdminAddHeadtext($params)
	{
		//audit('', 'GoogleAuthenticator', 'admin_add_headtext fired');
		$this->Enforce2FA();
		return ''; // do not output JS/CSS — just trigger logic
	}
	
	public function DoEvent($originator, $eventname, &$params)
	{
		audit('', 'GoogleAuthenticator', "DoEvent: originator=$originator, event=$eventname");

		if ($originator == 'Core' && $eventname == 'LoginPost') {
			//audit('', 'GoogleAuthenticator', "HandleLoginPost being called");
			$this->HandleLoginPost($params);
			return;
		}

		if ($originator == 'Core' && $eventname == 'LogoutPost') {
			//audit('', 'GoogleAuthenticator', "HandleLogoutPost being called");
			$this->HandleLogoutPost($params);
			return;
		}
	}

	
	private function Enforce2FA()
	{
		$userid = get_userid();
		if (!$userid) return;
		if (empty($_SESSION[CMS_USER_KEY])) return;

		// 2FA disabled?
		if (!$this->IsUser2FAEnabled($userid)) return;
		
		if ($this->GetPreference('allow_root_bypass', 1)) {
			return;
		}

		// Already verified?
		if (!empty($_SESSION[self::SESSION_2FA_VERIFIED]) &&
			$_SESSION[self::SESSION_2FA_VERIFIED] == $userid) {
			return;
		}

		// Mark as needing MFA
		$_SESSION[self::SESSION_TEMP_USER] = $userid;

		// Avoid looping on verify pages
		$req = $_SERVER['REQUEST_URI'] ?? '';
		if (strpos($req, 'verify_2fa') !== false ||
			strpos($req, 'process_verify_2fa') !== false) {
			return;
		}

		$config = cmsms()->GetConfig();
		$admin_url = $config['root_url'] . '/' . $config['admin_dir'];

		// --- FIX: PRESERVE CMSMS ADMIN SECURITY TOKEN ---
		$secure_param = CMS_SECURE_PARAM_NAME;
		$secure_value = $_GET[$secure_param] ?? $_SESSION[$secure_param] ?? '';
		
		$token = '';
		if ($secure_value) {
			$token = "&{$secure_param}={$secure_value}";
		}

		// Correct MFA redirect (token included)
		$verify_url = $admin_url 
					. '/moduleinterface.php?mact=GoogleAuthenticator,m1_,verify_2fa,0'
					. $token;

		redirect($verify_url);
		exit;
	}

    /**
     * Handle post-login event to check 2FA requirement
     */
	private function HandleLoginPost($params)
	{
		$userid = $params['user']->id ?? 0;
		if (!$userid) return;

		//audit('', 'GoogleAuthenticator', "HandleLoginPost: userid={$userid}");

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

		//audit('', 'GoogleAuthenticator', "Set SESSION_TEMP_USER={$userid}");
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

		$encrypted = $db->GetOne($query, array((int)$userid));
		return $this->DecryptSecret($encrypted);
	}


    /**
     * Save user's 2FA secret
     */
    public function SaveUserSecret($userid, $secret, $enabled = false)
	{
		$db = \cms_utils::get_db();

		$userid = (int)$userid;
		$enabled_flag = $enabled ? 1 : 0;
		$encrypted = $this->EncryptSecret($secret);

		// Check if record exists
		$query = "SELECT user_id FROM " . cms_db_prefix() . "module_ga_users WHERE user_id = ?";
		$exists = $db->GetOne($query, array($userid));

		if ($exists) {
			$query = "UPDATE " . cms_db_prefix() . "module_ga_users 
					  SET secret = ?, enabled = ?, modified_date = NOW() 
					  WHERE user_id = ?";
			$db->Execute($query, array($encrypted, $enabled_flag, $userid));
		} else {
			$query = "INSERT INTO " . cms_db_prefix() . "module_ga_users 
					  (user_id, secret, enabled, created_date, modified_date) 
					  VALUES (?, ?, ?, NOW(), NOW())";
			$db->Execute($query, array($userid, $encrypted, $enabled_flag));
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
    public function GenerateSecret($length = 32)
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
	
	private function EncryptSecret($plaintext)
	{
		$key = $this->GetEncryptionKey();
		$iv = random_bytes(16); // AES block size

		$ciphertext = openssl_encrypt($plaintext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

		// Store iv + ciphertext together (base64)
		return base64_encode($iv . $ciphertext);
	}

	/**
	 * Decrypt a secret retrieved from the database
	 */
	private function DecryptSecret($stored)
	{
		if (!$stored) return null;

		$key = $this->GetEncryptionKey();
		$raw = base64_decode($stored);

		if (!$raw || strlen($raw) < 17) return null;

		$iv = substr($raw, 0, 16);
		$ciphertext = substr($raw, 16);

		return openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
	}

	/**
	 * Load or generate a stable encryption key
	 */
	private function GetEncryptionKey()
	{
		$key = $this->GetPreference('enc_key', '');
		if ($key === '') {
			// 32 bytes = 256-bit key
			$key = bin2hex(random_bytes(32));
			$this->SetPreference('enc_key', $key);
		}
		return hex2bin($key);
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
