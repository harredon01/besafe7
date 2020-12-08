<div>
    Zonas<br><br>
    

    <div class="mapcont">
        <div id="map"></div>
    </div>
    <ul>
        <li id="item-@{{ item.id}}" ng-repeat="item in items">
            Id: <span class="type">@{{ item.id}}</span><br/>
            lat: <span class="type">@{{ item.lat}}</span><br/>
            Proveedor: <span class="type">@{{ item.provider}}</span><br/> 
            long: <span class="type">@{{ item.long}}</span><br/>
            active: <span class="type">@{{ item.isActive}}</span><br/>
            <a href="javascript:;" ng-click="selectItem(item)" class="editar">Select</a><br/>
            <a href="javascript:;" ng-click="updateItem(item)" class="editar"  ng-if="item.isActive">Save</a><br  ng-if="item.isActive"/>
            <a href="javascript:;" ng-click="deleteItem(item)" class="editar">Delete</a><br/><br/><br/>
        </li>
        <li ng-show="loadMore">
            <button ng-click="getItems()">Cargar mas</button>
        </li>
    </ul>
</div>
<div>

</div>


