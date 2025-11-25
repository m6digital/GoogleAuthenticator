<h2>Manage User 2FA Status</h2>

<table class="pagetable">
    <thead>
        <tr>
            <th>User ID</th>
            <th>Username</th>
            <th>Name</th>
            <th>Email</th>
            <th>2FA Enabled?</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
    {foreach from=$rows item=row}
        <tr>
            <td>{$row.userid}</td>
            <td>{$row.username}</td>
            <td>{$row.fullname}</td>
            <td>{$row.email}</td>
            <td>
                {if $row.enabled}
                    <span style="color:green;font-weight:bold;">YES</span>
                {else}
                    <span style="color:red;font-weight:bold;">NO</span>
                {/if}
            </td>
            <td>{$row.actions}</td>
        </tr>
    {/foreach}
    </tbody>
</table>
