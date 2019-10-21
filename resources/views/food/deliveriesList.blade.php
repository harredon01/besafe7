<div style="height:400px; overflow:scroll">
    Entregas(@{{ deliveries.length}})<br><br>
    <ul>
        <li id="item-@{{ item.id}}" ng-repeat="item in deliveries">
            Id: <span class="type">@{{ item.id}}</span><br/>
            Direccion: <span class="type">@{{ item.address.address}}</span><br/>
            Direccion id: <span class="type">@{{ item.address.id}}</span><br/>
            fecha: <span class="type">@{{ item.delivery | date}}</span><br/>
            Usuario: <span class="type">@{{ item.user.firstName}} @{{ item.user.lastName}}</span><br/>
            Email: <span class="type">@{{ item.user.email}}</span><br/>
            Cel: <span class="type">@{{ item.user.cellphone}}</span><br/>
            Transporte: <span class="type">@{{ item.shipping}}</span><br/>
            <div ng-if="item.build">
                <select ng-model="item.type_id" name="type_id" ng-change="selectMissingType(item)">
                    <option ng-repeat="article in listArticles" value="@{{article.id}}">@{{article.name}}</option>
                </select>
                <select ng-model="item.starter_id" name="starter_id">
                    <option ng-repeat="starter in item.starters" value="@{{starter.codigo}}">@{{starter.valor}}</option>
                </select>
                <select ng-model="item.main_id" name="main_id">
                    <option ng-repeat="main in item.mains" value="@{{main.codigo}}">@{{main.valor}}</option>
                </select>
                <button ng-click="selectDish(item)">Actualizar</button>
            </div>
            <table>
                <tr ng-repeat="(key, value) in item.details">
                    <td> @{{key}} </td> <td> @{{ value}} </td>
                </tr>
            </table>
            <input type="tel" name="destination" ng-model="item.destination" />
                        <button ng-click="updateDeliveryAddress(item.user.id,item.destination)">Actualizar</button>

        </li>
        <li ng-show="showMore">
            <button ng-click="getItems()">Cargar mas</button>
        </li>
    </ul>
</div>


