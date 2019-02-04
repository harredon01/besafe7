<div>
    Zonas<br><br>
    <ul>
        <li id="item-@{{ item.id}}" ng-repeat="item in items">
            Id: <span class="type">@{{ item.id}}</span><br/>
            lat: <span class="type">@{{ item.lat}}</span><br/>
            long: <span class="type">@{{ item.long}}</span><br/>
            <ul>
                <li id="item-@{{ item.id}}" ng-repeat="point in item.coverage">@{{ point }}</li>
            </ul>
            <br/><a href="javascript:;" ng-click="editDish(item)" class="editar">Editar</a>
        </li>
        <li ng-show="loadMore">
            <button ng-click="getItems()">Cargar mas</button>
        </li>
    </ul>
</div>
<div>
    
</div>


