<table >
    <tr>
        <th class="col-md-3">
            Nombre
        </th>
        <th class="col-md-2">
            Precio:
        </th>
        <th class="col-md-2">

        </th>
    </tr>
    <tr ng-repeat="item in shipping">
        <td class="col-md-3">
            @{{ item.platform}}
        </td>
        <td class="col-md-2">
            @{{ item.price | currency }}
        </td>
        <td class="col-md-2">                              
            <a href="javascript:;" ng-click="setShippingCondition(item)">Usar</a>
        </td>
    </tr>
</table>

