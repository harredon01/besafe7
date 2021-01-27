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
            @{{ item.platform}}<br/><p class="text-gray">@{{ item.desc}}</p>
        </td>
        <td class="col-md-2">
            @{{ item.price | currency }}
        </td>
        <td class="col-md-2">   
            <a href="javascript:;" class="text-black" ng-show="item.selected"><i class="ion-checkmark"></i></a>
            
            <a href="javascript:;" class="text-primary" ng-hide="item.selected" ng-click="setShippingCondition(item)">Usar</a>
        </td>
    </tr>
</table>

