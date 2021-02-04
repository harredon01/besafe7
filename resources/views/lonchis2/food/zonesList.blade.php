<div>
    Zonas<br><br>
    

    <div class="mapcont">
        <div id="map" style="height: 500px"></div>
    </div>
    <ul>
        <li id="item-@{{ item.id}}" ng-repeat="item in items">
            Id: <span class="type">@{{ item.id}}</span><br/>
            lat: <span class="type">@{{ item.lat}}</span><br/>
            long: <span class="type">@{{ item.long}}</span><br/>
            active: <span class="type">@{{ item.isActive}}</span><br/>
            <br/><a href="javascript:;" ng-click="selectItem(item)" class="editar">Select</a>
            <br  ng-if="item.isActive"/><a href="javascript:;" ng-click="updateItem(item)" class="editar"  ng-if="item.isActive">Save</a>
            <br/><a href="javascript:;" ng-click="deleteItem(item)" class="editar">Delete</a>
        </li>
        <li ng-show="loadMore">
            <button ng-click="getItems()">Cargar mas</button>
        </li>
    </ul>
</div>
<div>

</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/angular-google-maps/2.3.2/angular-google-maps.min.js" async></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCOlc_3d8ygnNCMRzfEpmvSNsYtmbowtYo"></script>

