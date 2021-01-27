<div class="col-12">
    <div class="checkout-cart-total">
        <h2 class="checkout-title">Tu Pedido</h2>
        <h4>Producto <span>Total</span></h4>

        <ul>
            <li ng-repeat="item in items"><span class="left">@{{ item.name}} X @{{ item.quantity}}</span> <span class="right">@{{ item.priceSumWithConditions | currency }}</span></li>
        </ul>

        <p>Sub Total <span>@{{subtotal| currency }}</span></p>
        <p ng-show="shipping > 0">Domicilio<span>@{{shipping| currency }}</span></p>
        <p ng-show="discount > 0">Descuentos <span>@{{discount| currency }}</span></p>
        <h4>Total <span>@{{total| currency }}</span></h4>
        <br/>
        <div ng-hide="paymentActive" id="final-submit">
            <div><a href="javascript:;" ng-click="couponVisible = true" class="text-primary" style="font-size: 17px">¡Tengo un cupón!</a></a></div>
            <div ng-show="couponVisible">
                <label style="float:left;width:100%">Ingresa un cupon</label>
                <input type="text" ng-model="coupon" style="border:1px solid black;width:50%;float:left;height:36px" class="form-control" name="coupon" required>
                <button ng-click="setCoupon()" style="float:right" class="btn btn-primary">Enviar</button>
            </div>
            <div style="clear:both"></div>
            <br/>
            <div class="term-block" ng-show="shippingConditionSet && bookingSet || isDigital && bookingSet">
                <input type="checkbox" ng-model="accept" id="accept_terms2">
                <label for="accept_terms2">He leido y acepto los <a style="color:#56a700" href="/a/terms" target="_blank">términos y condiciones</a></label>
            </div>
            <button class="place-order w-100"  ng-show="shippingConditionSet && bookingSet || isDigital && bookingSet" ng-click="prepareOrder()">Pagar Orden</button>
            <p ng-show="acceptError">Debes aceptar los términos y condiciones para continuar</p>
        </div>

    </div>
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

