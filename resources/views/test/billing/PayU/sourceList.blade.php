
<div>
    Tus Tarjetas <br>
    <ul>

        <li id="source-@{{ source.id }}" ng-repeat="source in sources">
            Type: <span class="type">@{{ source.type }}</span><br/>
            Name: <span class="name">@{{ source.name }}</span><br/>
            Number: <span class="number">@{{ source.number }}</span>
            <br ng-hide="source.is_default || buying"/><a href="javascript:;" ng-click="setAsDefault(source)" ng-hide="source.is_default || buying" class="editar">Usar para quick Pay</a>
            <br ng-hide="buying" /><a ng-hide="buying" href="javascript:;" ng-click="deleteSource(source)" class="editar">Borrar</a>
            <br ng-show="buying"/><a href="javascript:;" ng-click="selectSource(source)" ng-show="buying"class="editar">Usar</a>
        </li>

    </ul>
</div>



