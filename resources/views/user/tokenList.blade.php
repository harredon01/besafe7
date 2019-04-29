
<div>
    Listed Tokens<br><br>
    <table>
        <tr>
            <th>Client Id</th> 
            <th>Client Name</th>
            <th>Name</th> 
            <th>Created At</th>
            <th>Updated At</th>
            <th>Actions</th>
        </tr>
        <tr ng-repeat="token in tokens">
            <td><span class="name">@{{ token.client_id }}</span></td>
            <td><span class="name">Lonchis App</span></td>
            <td><span class="name">@{{ token.name }}</span></td>
            <td><span class="created_at">@{{ token.created_at }}</span></td>
            <td><span class="updated_at">@{{ token.updated_at }}</span></td>
               <td> <br/><a href="javascript:;" ng-click="deleteToken(token.id)" class="editar">Borrar</a></td>
        </tr>
    </table>
</div>


