<div class="col-lg-6 col-md-6" ng-controller="CheckoutCartCtrl" id="checkout-cart">
    <form action="#">    
        <h3>Tu pedido</h3> 
        <div class="order_table table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="item in items">
                        <td> @{{ item.name}}<strong> × @{{ item.quantity}}</strong></td>
                        <td> @{{ item.priceSumWithConditions | currency }}</td>
                    </tr>
                </tbody>
                <thead>
                    <tr>
                        <th>Novedades</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="item in conditions">
                        <td> @{{ item.getName}}</td>
                        <td> @{{ item.total | currency }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr ng-show="shipping>0">
                        <th>Envío</th>
                        <td><strong>@{{shipping| currency }}</strong></td>
                    </tr>
                    <tr class="order_total" ng-show="!hasTransactionCost">
                        <th>Total</th>
                        <td><strong>@{{total| currency }}</strong></td>
                    </tr>
                    <tr class="order_total" ng-show="hasTransactionCost">
                        <th>Transacción</th>
                        <td><strong>@{{transactionCost| currency }}</strong></td>
                    </tr>
                    <tr class="order_total" ng-show="hasTransactionCost">
                        <th>Total</th>
                        <td><strong>@{{totalTransaction| currency }}</strong></td>
                    </tr>
                </tfoot>
            </table>     
        </div>
        <div class="payment_method" ng-hide="paymentActive" id="final-submit">
            <div><a href="javascript:;" ng-click="couponVisible = true" class="text-primary" style="font-size: 17px">¡Tengo un cupón!</a></a></div>
            <div ng-show="couponVisible">
                <label style="float:left;width:100%">Ingresa un cupon</label>
                <input type="text" ng-model="coupon" style="border:1px solid black;width:50%;float:left;height:36px" class="form-control" name="coupon" required>
                <button ng-click="setCoupon()" style="float:right" class="btn btn-primary">Enviar</button>
            </div>
            <div style="clear:both"></div>
            <div class="term-block" ng-show="shippingAddressSet && shippingConditionSet && bookingSet || isDigital && bookingSet">
                <input type="checkbox" ng-model="accept" id="accept_terms2">
                <label for="accept_terms2">He leido y acepto los <a style="color:#56a700" href="/a/terms" target="_blank">términos y condiciones</a></label>
            </div>
            <div class="order_button" ng-show="shippingAddressSet && shippingConditionSet && bookingSet || isDigital && bookingSet">
                <button ng-click="prepareOrder()" style="width: 100%">Pagar Orden</button>
                <p ng-show="acceptError">Debes aceptar los términos y condiciones para continuar</p>
            </div>    
        </div> 
    </form>         
</div>

<!--table>
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
            <span class="item-attributes" ng-show="item.attributes.length > 0">
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

            <a href="javascript:;" ng-click="deleteCartItem(item.id)">Borrar</a>
        </td>
    </tr>

</table>

<label ng-show="subtotal > 0">subtotal: @{{subtotal| currency }}</label><br/>
<label ng-show="shipping > 0">shipping: @{{shipping| currency }}</label><br ng-show="shipping > 0"/>
<label ng-show="tax > 0">tax: @{{tax| currency }}</label><br ng-show="tax > 0"/>
<label ng-show="sale > 0">sale: @{{sale| currency }}</label><br ng-show="sale > 0"/>
<label ng-show="coupon > 0">coupon: @{{coupon| currency }}</label><br ng-show="coupon > 0"/>
<label ng-show="discount > 0">discount: @{{discount| currency }}</label><br ng-show="discount > 0"/>
<label ng-show="total > 0">total: @{{total| currency }}</label><br ng-show="total > 0"/-->

