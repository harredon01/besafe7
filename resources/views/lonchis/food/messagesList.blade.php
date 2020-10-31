<div>
    Mensajes<br><br>
    <ul>
        <li id="item-@{{ item.id}}" ng-repeat="item in items">
            Id: <span class="type">@{{ item.id}}</span><br/>
            Idioma: <span class="type">@{{ item.language}}</span><br/>
            Codigo: <span class="type">@{{ item.code}}</span><br/>
            <p>@{{ item.body}}</p>
            <br/><a href="javascript:;" ng-click="editDish(item)" class="editar">Editar</a>
        </li>
        <li ng-show="loadMore">
            <button ng-click="getItems()">Cargar mas</button>
        </li>
    </ul>
</div>
<div>
    
</div>


