<div style="height:400px; overflow:scroll">
    Direcciones<br><br>
    <ul>
        <li id="address-@{{ address.id}}" ng-repeat="address in addresses">
            Direccion: <span class="type">@{{ address.address}}</span><br/>
            Proveedor transp: <span class="type">@{{ address.provider}}</span><br/>
            Total: <span class="type">@{{ address.total}}</span><br/>

            <a href="javascript:;" ng-click="delegateAddress(address,false)" class="editar">Delegar 1 vez</a><br/>
            <a href="javascript:;" ng-click="delegateAddress(address,true)" class="editar">Delegar todos</a><br/>
        </li>
        <li ng-show="showMore">
            <button ng-click="getRoutes()">Cargar mas</button>
        </li>
    </ul>
</div>


