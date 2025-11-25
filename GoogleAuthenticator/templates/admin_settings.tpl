<h3>Google Authenticator – Preferences</h3>

{form_start action="admin_settings_save"}

<table class="pagetable" cellspacing="0" cellpadding="4">
<tr>
  <td>App Display Name (as seen in Google Authenticator)</td>
  <td><input type="text" name="{$actionid}app_display_name" value="{$prefs.app_display_name}" size="40"></td>
</tr>


<tr>
  <td>Enable backup codes</td>
  <td><input type="checkbox" name="{$actionid}enable_backup_codes" {if $prefs.enable_backup_codes}checked{/if}></td>
</tr>

<tr>
  <td>Allowed TOTP drift (windows)</td>
  <td><input type="number" name="{$actionid}totp_discrepancy" value="{$prefs.totp_discrepancy}" min="0" max="5"></td>
</tr>

<tr>
  <td>Allow superadmin bypass (user 1)</td>
  <td><input type="checkbox" name="{$actionid}allow_root_bypass" {if $prefs.allow_root_bypass}checked{/if}></td>
</tr>

<tr>
  <td>Custom login message</td>
  <td><textarea name="{$actionid}login_message" rows="3" cols="50">{$prefs.login_message}</textarea></td>
</tr>

</table>

<p><input type="submit" value="Save Preferences" class="pagebutton" /></p>

{form_end}
