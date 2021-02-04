<div>
    Merchants<br><br>
    <ul>
        <li id="item-@{{ item.id}}" ng-repeat="item in items">
            Id: <span class="type">@{{ item.id}}</span><br/>
            lat: <span class="type">@{{ item.lat}}</span><br/>
            long: <span class="type">@{{ item.long}}</span><br/>
            name: <span class="type">@{{ item.name}}</span><br/>
            email: <span class="type">@{{ item.email}}</span><br/>
            telephone: <span class="type">@{{ item.telephone}}</span><br/>
            address: <span class="type">@{{ item.address}}</span><br/>
            description: <span class="type">@{{ item.description}}</span><br/>
            type: <span class="type">@{{ item.type}}</span><br/>
            <br/><a href="javascript:;" ng-click="editDish(item)" class="editar">Editar</a>
        </li>
        <li ng-show="loadMore">
            <button ng-click="getItems()">Cargar mas</button>
        </li>
    </ul>
</div>
<div>
    
</div>


