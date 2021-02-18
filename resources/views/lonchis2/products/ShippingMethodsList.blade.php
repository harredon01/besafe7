<table ng-show="shipping.length>0">
    <tr>
        <th class="col-md-3">
            Nombre
        </th>
        <th class="col-md-2">
            Precio:
        </th>
        <th class="col-md-2">
            Seleccionar
        </th>
    </tr>
    <tr ng-repeat="item in shipping" ng-hide="!item.price" style="border-top: 1px solid black">
        <td class="col-md-3">
            @{{ item.platform}}<br/><p class="text-gray">@{{ item.desc}} <span ng-show="item.ondelivery" style="color:red">y pago contraentrega</span></p>
        </td>
        <td class="col-md-2">
            @{{ item.price | currency }}
        </td>
        <td class="col-md-2">   
            <a href="javascript:;" class="text-black" ng-show="item.selected"><i class="ion-checkmark"></i></a>
            
            <a href="javascript:;" class="text-primary" ng-hide="item.selected" ng-click="setShippingCondition(item)"><i class="ion-android-radio-button-off"></i></a>
        </td>
    </tr>
</table>

