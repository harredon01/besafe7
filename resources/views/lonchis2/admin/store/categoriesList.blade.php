<div>
    Categories<br><br>
    <ul>
        <li id="item-@{{ item.id}}" ng-repeat="item in items">
            Id: <span class="type">@{{ item.id}}</span><br/>
            name: <span class="type">@{{ item.name}}</span><br/>
            <br/><a href="javascript:;" ng-click="editDish(item)" class="editar">Editar</a>
        </li>
        <li ng-show="loadMore">
            <button ng-click="getItems()">Cargar mas</button>
        </li>
    </ul>
</div>
<div>
    
</div>


