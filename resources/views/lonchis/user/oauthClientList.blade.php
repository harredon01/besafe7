
<div>
    Listed Oauth Clients<br><br>
    <table>
        <tr>
            <th>Id</th>
            <th>Name</th> 
            <th>Redirect</th>
            <th>Created At</th>
            <th>Updated At</th>
            <th>Actions</th>
        </tr>
        <tr id="oauthClient-@{{ oauthClient.id }}" ng-repeat="oauthClient in oauthClients">
            <td><span class="id">@{{ oauthClient.id }}</span></td>
            <td><span class="name">@{{ oauthClient.name }}</span></td>
            <td><span class="redirect">@{{ oauthClient.redirect }}</span></td>
            <td><span class="created_at">@{{ oauthClient.created_at }}</span></td>
            <td><span class="updated_at">@{{ oauthClient.updated_at }}</span></td>
            <td><br/><a href="javascript:;" ng-click="editOauthClient(oauthClient.id)" class="editar">Editar</a>
                <br/><a href="javascript:;" ng-click="deleteOauthClient(oauthClient.id)" class="editar">Borrar</a></td>
        </tr>
    </table>
</div>


