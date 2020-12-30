<div>
    Platos<br><br>
    <ul>
        <li id="item-@{{ item.id}}" ng-repeat="item in items">
            Id: <span class="type">@{{ item.id}}</span><br/>
            Nombre: <span class="type">@{{ item.name}}</span><br/>
            Descripcion: <span class="type">@{{ item.description}}</span><br/>
            Entradas:<br/>
            <table>
                <tr>
                    <td>Valor</td>
                    <td>Codigo</td>
                    <td>Descripcion</td>
                </tr>
                <tr ng-repeat="item in item.attributes.entradas">
                    <td> @{{item.valor}} </td> <td> @{{item.codigo}} </td><td> @{{item.descripcion}} </td>
                </tr>
            </table>
            Fuertes:<br/>
            <table>
                <tr>
                    <td>Valor</td>
                    <td>Codigo</td>
                    <td>Descripcion</td>
                </tr>
                <tr ng-repeat="item in item.attributes.plato">
                    <td> @{{item.valor}} </td> <td> @{{item.codigo}} </td><td> @{{item.descripcion}} </td>
                </tr>
            </table>
            <br/><a href="javascript:;" ng-click="editDish(item)" class="editar">Editar</a>
        </li>
        <li ng-show="loadMore">
            <button ng-click="getItems()">Cargar mas</button>
        </li>
    </ul>
</div>
<div>
    
</div>


