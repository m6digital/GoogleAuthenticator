<h3>{$mod->Lang('setup_2fa')}</h3>
<form method="post" action="{$actionurl}">
    <input type="hidden" name="mact" value="GoogleAuthenticator,admin_save,0,1">
    <p>{$mod->Lang('secret_key')}: <strong>{$secret}</strong></p>
    <p><img src="{$qrcode_url}" alt="QR Code"></p>
    <p>{$mod->Lang('verify_code')}: <input type="text" name="code" value=""></p>
    <p><input type="submit" name="submit" value="{$mod->Lang('submit')}"></p>
</form>
