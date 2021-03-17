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
    <tr ng-repeat="item in shipping" ng-hide="!item.price && item.platform != 'Domicilio del Establecimiento'">
        <td class="col-md-3">
            @{{ item.platform}}<br/><p class="text-gray">@{{ item.desc}} 
                <span ng-show="item.ondelivery&&item.platform != 'MiPaquete'" style="color:red">y pago contraentrega</span>
                <a ng-show="item.ondelivery&&item.platform == 'MiPaquete'" style="color:red" href="https://mipaquete.com/pago-contraentrega/" target="_blank">y pago contraentrega</a>
            </p>
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
<br/><br/>
<p ng-show="ondelivery">Recuerda que pagar en línea tiene un costo estimado transaccion payu de: <span class="text-primary"><strong>@{{ estimatedTransactionCost | currency}}</strong></span></p>
<p ng-show="ondelivery">Si quieres pagar <span class="text-primary"><strong>0</strong></span> transaccion puedes usar Nequi (Bancolombia) o una consignacion bancaria. </p>
