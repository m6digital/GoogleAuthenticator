<h2>Google Authenticator – Two-Factor Authentication</h2>

<p>Choose an option below:</p>

<ul class="cms_list">
    <li>{$links.enroll}</li>

    {if isset($links.backup)}
        <li>{$links.backup}</li>
    {/if}

    {if isset($links.admin_users)}
        <li style="margin-top:20px;"><strong>Admin Tools</strong></li>
        <li>{$links.admin_users}</li>
        <li>{$links.preferences}</li>
    {/if}
</ul>
