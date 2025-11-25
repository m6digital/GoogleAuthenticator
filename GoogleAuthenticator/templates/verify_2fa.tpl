<h2>Two-Factor Authentication Required</h2>

<p><em>New enrollments will have to re-enter the verification code upon first enrolling.</em></p>

<p>User: <strong>{$username}</strong></p>

<form method="post" action="{$submit_url}">
    <input type="hidden" name="{$actionid}userid" value="{$userid}">
    <p>Enter your 6-digit verification code:</p>
    <input type="text" name="{$actionid}code" maxlength="10" class="cms_textfield" style="width:120px;">
    <br><br>
    <input type="submit" value="Verify" class="cms_submit">
</form>
