<div>
    Ordenes<br><br>
    <ul>
        <li id="order-@{{ order.id}}" ng-repeat="order in orders">
            Id: <span class="type">@{{ order.id}}</span><br/>
            Email: <span class="type">@{{ order.email}}</span><br/>
            Status: <span class="type">@{{ order.status}}</span><br/>
            Items:<br/>
            <ul>
                <li id="route-@{{ order.id}}-stop-@{{ item.id}}" ng-repeat="item in order.items">
                    Id: <span class="type">@{{ item.id}}</span><br/>
                    Nombre: <span class="type">@{{ item.name}}</span><br/>
                    Precio: <span class="type">@{{ item.priceConditions}}</span><br/>
                    Cantidad: <span class="type">@{{ item.quantity}}</span><br/>
                    Subtotal: <span class="type">@{{ item.priceSumConditions}}</span><br/>
                    Atributos: <br/>
                    <table>
                        <tr ng-repeat="(key, value) in item.attributes">
                            <td> {{key}} </td> <td> {{ value}} </td>
                        </tr>
                    </table>
                </li>
            </ul>
            Condiciones:<br/>
            <ul>
                <li id="route-@{{ order.id}}-stop-@{{ item.id}}" ng-repeat="item in order.orderConditions">
                    Id: <span class="type">@{{ item.id}}</span><br/>
                    Nombre: <span class="type">@{{ item.name}}</span><br/>
                    Aplicado sobre: <span class="type">@{{ item.target}}</span><br/>
                    Regla: <span class="type">@{{ item.value}}</span><br/>
                    Total: <span class="type">@{{ item.total}}</span><br/>
                </li>
            </ul>
            Total: <span class="type">@{{ order.total}}</span><br/>
            <select>
                <option value="Programado">Programado</option>
                <option value="Transit">En transito</option>
                <option value="Delivered">Entregado</option>
            </select>
            <br/><a href="javascript:;" ng-click="approveOrder(order.id)" class="editar">Aprobar</a>
        </li>
        <li>
            <button>Cargar mas</button>
        </li>
    </ul>
</div>


