<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Carrito: @{{totalItems}}<span class="caret"></span></a>                    

<ul class="dropdown-menu dropdown-cart" role="menu">
    <table>
        <tr>
            
            <th class="col-md-3">
                Nombre
            </th>
            <th class="col-md-2">
                Cantidad:
            
            </th>
            <th class="col-md-2">
                Precio:
            </th>
            <th class="col-md-2">
               total:
            </th>
            <th class="col-md-2">
               
            </th>
        </tr>
    
    <tr ng-repeat="item in items">
            
            <td class="col-md-3">
                @{{ item.name}}
                <span class="item-attributes" ng-show="item.attributes.length>0">
                    @{{ item.attributes}}
                </span>
            </td>
            <td class="col-md-2">
                
            <input type="text" class="form-control" name="quantity-@{{ item.id}}" value="@{{ item.quantity}}" >
            <a href="javascript:;" ng-click="updateCartItem(item.id)">Actualizar</a><br/>
            </td>
            <td class="col-md-2">
                @{{ item.priceConditions | currency }}
            </td>
            <td class="col-md-2">
               @{{ item.priceSumConditions | currency }}
            </td >
            <td class="col-md-2">
               
           <a href="javascript:;" ng-click="deleteCartItem( item.id)">Borrar</a>
            </td>
        </tr>
    

    </table>
    <a href="javascript:;" ng-click="clearCart()">Limpiar Carrito</a>
</ul>

