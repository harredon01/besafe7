<div>
    Rutas<br><br>
    <ul>
        <li id="route-@{{ route.id}}" ng-repeat="route in routes">
            Id: <span class="type">@{{ route.id}}</span><br/>
            Type: <span class="type">@{{ route.type}}</span><br/>
            Name: <span class="type">@{{ route.name}}</span><br/>
            Status: <span class="type">@{{ route.status}}</span><br/>
            <ul>
                <li id="route-@{{ route.id}}-stop-@{{ stop.id}}" ng-repeat="stop in route.stops">
                    Id: <span class="type">@{{ stop.id}}</span><br/>
                    Status: <span class="type">@{{ stop.status}}</span><br/>
                    Details: <span class="type">@{{ stop.details}}</span><br/>
                    <table>
                        <tr ng-repeat="(key, value) in stop.details">
                            <td> {{key}} </td> <td> {{ value}} </td>
                        </tr>
                    </table>
                    <input type="tel" name="Route"/>
                    <button ng-click="updateRouteStop()">Actualizar</button>
                </li>
            </ul>
            <br/><a href="javascript:;" ng-click="buildRoute(route.id)" class="editar">Construir</a>
        </li>
        <li>
            <button ng-click="loadMore()">Cargar mas</button>
        </li>
    </ul>
</div>


