<div style="height:400px; overflow:scroll">
    Rutas<br><br>
    <ul>
        <li id="route-@{{ route.id}}" ng-repeat="route in routes">
            <h3>Ruta Id: <span class="type">@{{ route.id}}</span></h3><br/>
            Type: <span class="type">@{{ route.type}}</span><br/>
            Status: <span class="type">@{{ route.status}}</span><br/>
            Costo Envio: <span class="type">@{{ route.unit_cost}}</span><br/>
            Ingreso Envio: <span class="type">@{{ route.unit_price}}</span><br/>
            Almuerzos: <span class="type">@{{ route.unit}}</span><br/>
            Stops
            <table>
                <tr>
                    <td>
                        Id
                    </td>
                    <td>
                        Status
                    </td>
                    <td>
                        Amount
                    </td>
                    <td>
                        Envio
                    </td>
                    <td>
                        Detalles
                    </td>
                    <td>
                        Acciones
                    </td>
                </tr>
                <tr ng-repeat="stop in route.stops">
                    <td>
                        @{{ stop.id}}
                    </td>
                    <td>
                        @{{ stop.status}}
                    </td>
                    <td>
                        @{{ stop.amount}}
                    </td>
                    <td>
                        @{{ stop.shipping}}
                    </td>
                    <td>
                        Detalles Parada: 
                    <table>
                        <tr ng-repeat="(key, value) in stop.details">
                            <td> @{{key}} </td> <td> @{{ value}} </td>
                        </tr>
                    </table>
                    </td>
                    <td><input type="tel" name="Route" ng-model="stop.route_id" ng-show="stop.amount>0"/>
                    <button ng-click="updateRouteStop()" ng-show="stop.amount>0">Actualizar</button></td>
                </tr>
            </table>
            <!--ul>
                <li id="route-@{{ route.id}}-stop-@{{ stop.id}}" ng-repeat="stop in route.stops">
                    Id: <span class="type">@{{ stop.id}}</span><br/>
                    Status: <span class="type">@{{ stop.status}}</span><br/>
                    Amount: <span class="type">@{{ stop.amount}}</span><br/>
                    Envio: <span class="type">@{{ stop.shipping}}</span><br/>
                    Detalles Parada: 
                    <table>
                        <tr ng-repeat="(key, value) in stop.details">
                            <td> @{{key}} </td> <td> @{{ value}} </td>
                        </tr>
                    </table>
                    <ul>
                        <li id="route-@{{ route.id}}-stop-@{{ stop.id}}-delivery-@{{ delivery.id}}" ng-repeat="delivery in stop.deliveries">
                            Id: <span class="type">@{{ delivery.id}}</span><br/>
                            Direccion Id: <span class="type">@{{ delivery.address_id}}</span><br/>
                            Pago Envio: <span class="type">@{{ delivery.shipping}}</span><br/>
                            Detalles envio: 
                            <table>
                                <tr ng-repeat="(key2, value2) in delivery.details.dish">
                                    <td> @{{key2}} </td> <td> @{{ value2}} </td>
                                </tr>
                            </table>
                        </li>
                    </ul>
                    <input type="tel" name="Route" ng-model="stop.route_id" ng-show="stop.amount>0"/>
                    <button ng-click="updateRouteStop()" ng-show="stop.amount>0">Actualizar</button>
                </li>
            </ul-->
            <br/><a href="javascript:;" ng-click="buildRoute(route)" class="editar">Construir</a>
            <br/><br/><a href="javascript:;" ng-click="showRoute(route)" class="editar">Show Route</a>
        </li>
        <li ng-show="showMore">
            <button ng-click="getRoutes()">Cargar mas</button>
        </li>
    </ul>
</div>


