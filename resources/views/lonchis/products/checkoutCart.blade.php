
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
                
            <input type="text" class="form-control" name="check-quantity-@{{ item.id}}" value="@{{ item.quantity}}" >
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

<label ng-show="subtotal>0">subtotal: @{{subtotal | currency }}</label><br/>
                <label ng-show="shipping>0">shipping: @{{shipping | currency }}</label><br ng-show="shipping>0"/>
                <label ng-show="tax>0">tax: @{{tax | currency }}</label><br ng-show="tax>0"/>
                <label ng-show="sale>0">sale: @{{sale | currency }}</label><br ng-show="sale>0"/>
                <label ng-show="coupon>0">coupon: @{{coupon | currency }}</label><br ng-show="coupon>0"/>
                <label ng-show="discount>0">discount: @{{discount | currency }}</label><br ng-show="discount>0"/>
                <label ng-show="total>0">total: @{{total | currency }}</label><br ng-show="total>0"/>

