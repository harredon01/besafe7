<div>
    Rutas<br><br>
    <div class="ruta">
        <div id="route-@{{ route.id}}" class="route-detail" ng-repeat="route in routes" data-route="@{{ route.id}}">
            <h3>Ruta Id: <span class="type">@{{ route.id}}</span></h3><br/>
            Costo Envio: <span class="type">@{{ route.unit_cost}}</span><br/>
            Ingreso Envio: <span class="type">@{{ route.unit_price}}</span><br/>
            Almuerzos: <span class="type">@{{ route.unit}}</span><br/>
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
                    <tr ng-repeat="stop in route.stops">
                        <td>
                            @{{ stop.id}}
                        </td>
                        <td>
                            @{{ stop.amount}}
                        </td>
                        <td>
                            @{{ stop.shipping}}
                        </td>
                        <td><input type="tel" name="Route" ng-model="stop.route_id"/>
                            <button ng-click="updateRouteStop(stop)">Actualizar</button></td>
                        <td><button ng-click="sendStopToNewRoute(stop.id)">Nueva Ruta</button></td>
                        <td><button ng-click="deleteStop(stop.id)">Borrar parada</button></td>
                    </tr>
                </tbody>
            </table>
            <br/><a href="javascript:;" ng-click="buildRoute(route)" class="editar">Construir</a>
            <br/><br/><a href="javascript:;" ng-click="showRoute(route)" class="editar">Show Route</a>
            <br/><br/><a href="javascript:;" ng-click="addReturnStop(route.id)" class="editar">addReturnStop</a>
            <br/><br/><a href="javascript:;" ng-click="deleteRoute(route.id)" class="editar">deleteRoute</a>

        </div>
        <script>
            $(function () {
                setTimeout(function () {
                    $(".route-detail").each(function () {
                        var route_id = $(this).data("route");
                        console.log("Ruta ", route_id)
                        $('#route-' + route_id + '-table').sortable();
                    });
                }, 1000);
            });
        </script>
        <div ng-show="showMore">
            <button ng-click="getRoutes()">Cargar mas</button>
        </div>
    </div>
</div>


