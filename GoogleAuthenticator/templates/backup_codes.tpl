<h3>Your Backup Codes</h3>

{if isset($smarty.get.regen)}
<div class="pagemessage">New backup codes generated.</div>
{/if}


<p>
These codes may be used if you lose access to your Google Authenticator app.
Each code can be used <strong>one time only</strong>.
</p>

<table class="pagetable">
    <thead>
        <tr>
            <th>Code</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        {foreach $codes as $c}
        <tr>
            <td><code>{$c.code}</code></td>
            <td>
                {if $c.used == 1}
                    <span style="color: red;">Used</span>
                {else}
                    <span style="color: green;">Valid</span>
                {/if}
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>

<br>

<div>
    {$regen_link}
</div>
