<div>
    Zonas<br><br>
    <select ng-model="activeProvider" ng-change="changeScenario()">
        <option ng-repeat="provider in providers" value="@{{ provider.value}}">@{{ provider.name}}</option>
    </select><br/>
    <select ng-model="activeMerchant" ng-change="changeScenario()">
        <option ng-repeat="merchant in merchants" value="@{{ merchant.value}}">@{{ merchant.name}}</option>
    </select><br/>
    <a href="javascript:;" ng-click="createItem()" class="editar">Create Item</a><br/>
    <div class="mapcont">
        <div id="map"></div>
    </div>
    <ul>
        <li id="item-@{{ item.id}}" ng-repeat="item in items">
            Id: <span class="type">@{{ item.id}}</span><br/>
            lat: <span class="type">@{{ item.lat}}</span><br/>
            long: <span class="type">@{{ item.long}}</span><br/>
            <br/><a href="javascript:;" ng-click="selectItem(item)" class="editar">Select</a>
            <br/><a href="javascript:;" ng-click="updateItem(item)" class="editar">Save</a>
            <br/><a href="javascript:;" ng-click="deleteItem(item)" class="editar">Delete</a>
        </li>
        <li ng-show="loadMore">
            <button ng-click="getItems()">Cargar mas</button>
        </li>
    </ul>
</div>
<div>

</div>


