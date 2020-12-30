<div>
    Ordenes<br><br>
    <ul>
        <li id="order-@{{ order.id}}" ng-repeat="order in orders">
            Id: <span class="type">@{{ order.id}}</span><br/>
            Fecha Ultima Actualizacion: <span class="type">@{{ order.updated_at}}</span><br/>
            Fecha Creacion: <span class="type">@{{ order.created_at}}</span><br/>
            Status: <span class="type">@{{ order.status}}</span><br/>
            Total: <span class="type">@{{ order.total}}</span><br/>

            User:
            Nombre: <span class="type">@{{ order.user.name}}</span><br/>
            Email: <span class="type">@{{ order.user.email}}</span><br/>
            Celular: <span class="type">@{{ order.user.cellphone}}</span><br/>

            Items:<br/>
            <table>
                <tr>
                    <td>Id</td>
                    <td>Nombre</td>
                    <td>Precio</td>
                    <td>Cantidad</td>
                    <td>Subtotal</td>
                    <td>Atributos</td>
                </tr>
                <tr ng-repeat="item in order.items">
                    <td>@{{ item.id}}</td>
                    <td>@{{ item.name}}</td>
                    <td>@{{ item.priceConditions}}</td>
                    <td>@{{ item.quantity}}</td>
                    <td>@{{ item.priceSumConditions}}</td>
                    <td><table>
                            <tr ng-repeat="(key, value) in item.attributes">
                                <td> @{{key}} </td> <td> @{{ value}} </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            Condiciones:<br/>

            <table>
                <tr>
                    <td>Id</td>
                    <td>Nombre</td>
                    <td>Aplicado sobre</td>
                    <td>Regla</td>
                    <td>Total</td>
                </tr>
                <tr ng-repeat="item in order.order_conditions">
                    <td>@{{ item.id}}</td>
                    <td>@{{ item.name}}</td>
                    <td>@{{ item.target}}</td>
                    <td>@{{ item.value}}</td>
                    <td>@{{ item.total}}</td>
                </tr>
            </table>

            Total: <span class="type">@{{ order.total}}</span><br/>
            Payments: <br/>
            <ul>
                <li id="order-@{{ order.id}}-@{{ payment.id}}" ng-repeat="payment in order.payments">
                    Payment Id: <span class="type">@{{ payment.id}}</span><br/>
                    Total: <span class="type">@{{ payment.total}}</span><br/>
                    Status: <span class="type">@{{ payment.status}}</span><br/>
                    Fecha Ultima Actualizacion: <span class="type">@{{ payment.updated_at}}</span><br/>
                    Fecha Creacion: <span class="type">@{{ payment.created_at}}</span><br/>
                </li>
            </ul>
            <select ng-model="order.status">
                <option value="approved">Aprobado</option>
                <option value="Approved">Aprobado</option>
                <option value="Programado">Programado</option>
                <option value="Transit">En transito</option>
                <option value="Delivered">Entregado</option>
            </select>
            <br/><a href="javascript:;" ng-click="approveOrder(order.id)" class="editar">Aprobar</a><br/><br/><br/>
        </li>
        <li ng-show="showMore">
            <button ng-click="getOrders()">Cargar mas</button>
        </li>
    </ul>
</div>


