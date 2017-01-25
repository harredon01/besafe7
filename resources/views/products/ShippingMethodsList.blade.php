<h3>Selecciona un metodo de envio</h3>
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
    
    <tr ng-repeat="item in shippingMethods">
            
            <td class="col-md-3">
                @{{ item.name}}
            </td>
            <td class="col-md-2">
                @{{ item.value | currency }}
            </td>
            <td class="col-md-2">
               <span class="shipping">Seleccionada: @{{ item.selected }}</span>
                               
           <a href="javascript:;" ng-click="setShippingCondition( item.id)">Usar</a>
            </td>
        </tr>
    

    </table>

