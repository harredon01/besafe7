<div style="height:400px; overflow:scroll">
    Entregas<br><br>
    <ul>
        <li id="item-@{{ item.id}}" ng-repeat="item in deliveries">
            Id: <span class="type">@{{ item.id}}</span><br/>
            Direccion: <span class="type">@{{ item.address.address}}</span><br/>
            Direccion id: <span class="type">@{{ item.address.id}}</span><br/>
            fecha: <span class="type">@{{ item.delivery | date}}</span><br/>
            Usuario: <span class="type">@{{ item.user.firstName}} @{{ item.user.lastName}}</span><br/>
            Transporte: <span class="type">@{{ item.shipping}}</span><br/>
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


