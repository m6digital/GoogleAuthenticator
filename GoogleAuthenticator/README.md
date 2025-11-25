<h1>GoogleAuthenticator — Two-Factor Authentication for CMS Made Simple</h1>

<h2>Overview</h2>
<p>
GoogleAuthenticator adds secure Two-Factor Authentication (2FA) to the CMS Made Simple admin panel using 
<strong>time-based one-time passwords (TOTP)</strong>. Administrators can require MFA for enhanced security, 
while users can self-enroll, manage backup codes, and reset their secret.
</p>
<p>
The module integrates deeply with CMSMS’s login flow, preventing access to the admin area until a valid MFA code is entered.
</p>

<h2>Features</h2>

<h3>User Self-Enrollment</h3>
<ul>
    <li>Generate a TOTP secret</li>
    <li>Scan a QR code into Google Authenticator, Authy, Microsoft Authenticator, etc.</li>
    <li>Verify initial setup with a 6-digit code</li>
    <li>Reset secret at any time</li>
    <li>Generate and view backup codes (if enabled)</li>
</ul>

<h3>Enforced MFA on Login</h3>
<ul>
    <li>After CMSMS validates credentials, the module intercepts admin access</li>
    <li>Checks if the user requires 2FA</li>
    <li>Redirects to the MFA verification screen</li>
    <li>Grants access only after successful verification</li>
</ul>

<h3>Backup Codes</h3>
<ul>
    <li>Optional (controlled by preferences)</li>
    <li>Create 10 one-time-use emergency codes</li>
    <li>Codes are logged, tracked, and marked when used</li>
</ul>

<h3>Admin User Management</h3>
<p>Admins with <strong>Manage GoogleAuthenticator</strong> permission can:</p>
<ul>
    <li>View all CMSMS admin users</li>
    <li>See MFA status</li>
    <li>Enroll users</li>
    <li>Disable MFA</li>
    <li>Reset secrets</li>
    <li>View backup codes</li>
    <li>Regenerate backup codes</li>
</ul>

<h3>Session-Level Security</h3>
<p>This module uses three secure session keys:</p>
<ul>
    <li><code>ga_temp_user</code> — user logged in but not yet MFA verified</li>
    <li><code>ga_2fa_verified</code> — user who completed MFA</li>
    <li><code>ga_redirect_url</code> — where to continue after MFA</li>
</ul>
<p>All values are cleared on logout.</p>

<h2>Preferences</h2>
<p>Located under: <strong>Extensions → Google Authenticator → Preferences</strong></p>

<h3>App Display Name</h3>
<p>Controls the name shown inside authenticator apps.<br>
Example: <em>M6 Digital Admin</em></p>

<h3>Enable Backup Codes</h3>
<ul>
    <li><strong>Enabled:</strong> Users can generate and use backup codes</li>
    <li><strong>Disabled:</strong> Only TOTP codes are accepted</li>
</ul>

<h3>Allowed TOTP Drift (Window)</h3>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>Value</th>
        <th>Meaning</th>
    </tr>
    <tr>
        <td>0</td>
        <td>Exact 30-second window</td>
    </tr>
    <tr>
        <td>1</td>
        <td>&plusmn;1 window (~1 minute total tolerance)</td>
    </tr>
    <tr>
        <td>2</td>
        <td>&plusmn;2 windows (~2 minutes total tolerance)</td>
    </tr>
</table>

<h3>Allow Root Bypass</h3>
<p>
Allows <strong>user ID 1</strong> to log in without MFA. Useful for emergencies, but 
<strong>should be disabled on production systems</strong>.
</p>

<h3>Login Message</h3>
<p>Custom text displayed on the 2FA prompt.<br>
Example: <em>Enter the 6-digit code from your authenticator app.</em></p>

<h2>Enrollment Workflow</h2>
<ol>
    <li>User opens <strong>Set Up / Manage My 2FA</strong></li>
    <li>Clicks <strong>Generate Secret</strong> or <strong>Reset Secret</strong></li>
    <li>Scans the QR code</li>
    <li>Enters the 6-digit verification code</li>
    <li>(Optional) Generates backup codes</li>
</ol>

<h2>Login Workflow</h2>
<ol>
    <li>User submits username & password</li>
    <li>CMSMS validates credentials</li>
    <li>GoogleAuthenticator intercepts admin access</li>
    <li>If MFA required → user is redirected to verification screen</li>
    <li>User enters TOTP code or backup code</li>
    <li>On success → user is redirected into admin</li>
</ol>

<h2>Database Schema</h2>

<h3><code>cms_module_ga_users</code></h3>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>Column</th><th>Description</th></tr>
    <tr><td>user_id</td><td>CMSMS user ID</td></tr>
    <tr><td>secret</td><td>Base32 TOTP secret</td></tr>
    <tr><td>enabled</td><td>1 = MFA required</td></tr>
    <tr><td>created_date</td><td>Timestamp</td></tr>
    <tr><td>modified_date</td><td>Timestamp</td></tr>
</table>

<h3><code>cms_module_ga_backup_codes</code></h3>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>Column</th><th>Description</th></tr>
    <tr><td>id</td><td>Row ID</td></tr>
    <tr><td>user_id</td><td>CMSMS user ID</td></tr>
    <tr><td>code</td><td>Backup code</td></tr>
    <tr><td>used</td><td>0/1 (whether redeemed)</td></tr>
    <tr><td>created_date</td><td>Timestamp</td></tr>
    <tr><td>used_date</td><td>Timestamp</td></tr>
</table>

<h2>Troubleshooting</h2>

<h3>Login Loops</h3>
<ul>
    <li>CMS_USER_KEY missing from session</li>
    <li>Secret exists but MFA not enabled</li>
    <li>Bad session save path</li>
    <li>Redirect logic incorrectly bypassed</li>
</ul>

<h3>TOTP Codes Not Working</h3>
<ul>
    <li>Ensure the phone clock is synced</li>
    <li>Increase TOTP drift window</li>
    <li>Verify the secret stored in DB matches the QR code</li>
    <li>Regenerate secret if QR code was cached</li>
</ul>

<h3>Backup Codes Not Working</h3>
<ul>
    <li>Backup codes must be enabled</li>
    <li>The code may already be used</li>
    <li>User session must match the owning user ID</li>
</ul>

<h2>Security Notes</h2>
<ul>
    <li>Consider encrypting secrets in the database</li>
    <li>Only admin users are subject to MFA</li>
    <li>Disable root bypass in production</li>
    <li>Avoid drift settings > 1 unless necessary</li>
</ul>
