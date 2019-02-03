<div style="height:400px; overflow:scroll">
    Direcciones<br><br>
    <ul>
        <li id="address-@{{ address.id}}" ng-repeat="address in addresses">
            Direccion: <span class="type">@{{ address.address}}</span><br/>
            Total: <span class="type">@{{ address.total}}</span><br/>

            <br/><a href="javascript:;" ng-click="delegateAddress(address.id)" class="editar">Delegar</a>
        </li>
        <li ng-show="showMore">
            <button ng-click="getRoutes()">Cargar mas</button>
        </li>
    </ul>
</div>


