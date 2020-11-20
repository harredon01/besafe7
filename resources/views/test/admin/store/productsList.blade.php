<div>
    Products<br><br>
    <ul>
        <li id="item-@{{ item.id}}" ng-repeat="item in items">
            Id: <span class="type">@{{ item.id}}</span><br/>
            name: <span class="type">@{{ item.name}}</span><br/>
            description: <span class="type">@{{ item.description}}</span><br/>
            <ul>
                <li id="item-@{{ item.id}}" ng-repeat="point in item.categories">@{{ point }}</li>
            </ul>
            <ul>
                <li id="item-@{{ item.id}}" ng-repeat="point in item.product_variants">@{{ point }}</li>
            </ul>
        </li>
        <li ng-show="loadMore">
            <button ng-click="getItems()">Cargar mas</button>
        </li>
    </ul>
</div>
<div>
    
</div>


