    {if isset($message)}
        <p class="message">{$message}</p>
    {/if}

    <h3>{$lang.googleauthenticator_config}</h3>
    <form method="post">
        <label for="qrcode_size">{$lang.googleauthenticator_qrcode_size}</label>
        <input type="text" name="qrcode_size" id="qrcode_size" value="{$qrcode_size}">
        <input type="submit" name="submit" value="{$lang.googleauthenticator_update_prefs}">
    </form>
    