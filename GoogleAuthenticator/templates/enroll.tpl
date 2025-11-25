<h2>Two-Factor Authentication Setup</h2>

{if $message}
<div class="p-2 alert alert-info">{$message}</div>
{/if}

<p>Scan this QR code into Google Authenticator.</p>

<div style="margin:20px 0;">
    <img src="{$qrurl}" alt="QR Code">
</div>


<hr>

<h3>Verify Your Code</h3>

<form method="post" action="{$module_action_url}">

    <input type="hidden" name="{$id}userid" value="{$userid}">

    <p>Enter the 6-digit code from your app:</p>
    <input type="text" name="{$id}code" maxlength="6"
           class="cms_textfield" style="width:120px;">

    <br><br>
    <input type="submit" name="{$id}submit" 
           value="Verify and Enable 2FA" class="cms_submit">
</form>
