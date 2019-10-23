<div>
    Rutas<br><br>
    <div class="ruta">
        <div id="route-@{{ route.id}}" class="route-detail" ng-repeat="route in routes" data-route="@{{ route.id}}">
            <div style="display:none">
                <h3>Ruta Id: <span class="type">@{{ route.id}}</span></h3><br/>
                Costo Envio: <span class="type">@{{ route.unit_cost}}</span><br/>
                Ingreso Envio: <span class="type">@{{ route.unit_price}}</span><br/>
                Almuerzos: <span class="type">@{{ route.unit}}</span><br/>
            </div>
            <table >
                <tr>
                    <td>
                        Ruta Id
                    </td>
                    <td>
                        Costo Envio
                    </td>
                    <td>
                        Ingreso Envio
                    </td>
                    <td>
                        Almuerzos
                    </td>

                </tr>
                <tr>
                    <td>
                        @{{ route.id}}
                    </td>
                    <td>
                        @{{ route.unit_cost}}
                    </td>
                    <td>
                        @{{ route.unit_price}}
                    </td>
                    <td>
                        @{{ route.unit}}
                    </td>
                </tr>
            </table>
            Stops
            <table >
                <tbody id="route-@{{ route.id}}-table">
                    <tr>
                        <td>
                            Id
                        </td>
                        <td>
                            Amount
                        </td>
                        <td>
                            Envio
                        </td>
                        <td>
                            Acciones
                        </td>
                        <td>
                            Acciones
                        </td>
                        <td>
                            Acciones
                        </td>
                    </tr>
                    <tr ng-repeat="stop in route.stops" data-route="@{{ stop.route_id}}" data-stop="@{{ stop.id}}">
                        <td>
                            @{{ stop.id}}
                        </td>
                        <td>
                            -@{{ stop.amount}}
                        </td>
                        <td>
                            @{{ stop.shipping}}
                        </td>
                        <td><input style="display:none" type="tel" name="Route" ng-model="stop.route_id"/>
                            <button style="display:none" ng-click="updateRouteStop(stop)">Actualizar</button><br style="display:none"/>-@{{ stop.address.address}}
                        </td>
                        <td><button ng-click="sendStopToNewRoute(stop.id)">Nueva Ruta</button></td>
                        <td><button ng-click="deleteStop(stop.id)">Borrar parada</button></td>
                    </tr>
                </tbody>
            </table>
            <table >
                <tr>
                    <td>
                        <a href="javascript:;" ng-click="buildRoute(route)" class="editar">Construir</a>
                    </td>
                    <td>
                        <a href="javascript:;" ng-click="showRoute(route)" class="editar">Show Route</a>
                    </td>
                    <td>
                        <a href="javascript:;" ng-click="addReturnStop(route.id)" class="editar">addReturnStop</a>
                    </td>
                    <td>
                        <a href="javascript:;" ng-click="deleteRoute(route.id)" class="editar">deleteRoute</a>
                    </td>

                </tr>
            </table>
            <div style="display:none">
                <br/><a href="javascript:;" ng-click="buildRoute(route)" class="editar">Construir</a>
                <br/><br/><a href="javascript:;" ng-click="showRoute(route)" class="editar">Show Route</a>
                <br/><br/><a href="javascript:;" ng-click="addReturnStop(route.id)" class="editar">addReturnStop</a>
                <br/><br/><a href="javascript:;" ng-click="deleteRoute(route.id)" class="editar">deleteRoute</a>
            </div>
        </div>
        <div ng-show="showMore">
            <button ng-click="getRoutes()">Cargar mas</button>
        </div>
    </div>
</div>


