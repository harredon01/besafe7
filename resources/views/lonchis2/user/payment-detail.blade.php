@extends(config("app.views").'.layouts.app')

@section('content')
<!--shopping cart area start -->
<div class="shopping_cart_area mt-70" ng-controller="PaymentDetailCtrl">
    <div class="container">  
        <div class="row">
            <div class="col-12">
                <div class="table_desc">
                    <div class="cart_page">
                        <table>
                            <thead>
                                <tr>
                                    <th class="product_thumbnail">Imagen</th>
                                    <th class="product_name">Producto</th>
                                    <th class="product_price">Precio</th>
                                    <th class="product_quantity">Cantidad</th>
                                    <th class="product_total">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr ng-repeat="item in payment.order.items">
                                    <td class="product_thumb"><a href="#"><img src="image/product/home-1/product-1.jpg" alt="Product"></a></td>
                                    <td class="product_name"><a href="#">@{{item.name}}</a></td>
                                    <td class="product-price"><span>@{{item.price| currency}}</span></td>
                                    <td class="product_quantity"><span>@{{item.quantity| currency}}</span></td>
                                    <td class="product_total"><span>@{{item.priceSumConditions| currency}}</span></td>
                                </tr>
                            </tbody>
                        </table>   
                        <h2>Condiciones</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th class="product_thumbnail">Imagen</th>
                                    <th class="product_name">Producto</th>
                                    <th class="product_price">Precio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr ng-repeat="item in payment.order.order_conditions">
                                    <td class="product_thumbnail"><a href="#">@{{item.name}}</a></td>
                                    <td class="product_name"><span>@{{item.type}}</span></td>
                                    <td class="product_price"><span>@{{item.value| currency}}</span></td>
                                </tr>
                            </tbody>
                        </table>   
                    </div>  
                    <div class="cart_submit">
                        <button type="submit">update cart</button>
                    </div>      
                </div>
            </div>
        </div>
        <!--coupon code area start-->
        <div class="coupon_area">
            <div class="row">
                <div class="col-lg-6 col-md-6">
                    <div class="coupon_code left">
                        <h3><span>Envio</span></h3>
                        <div class="coupon_inner">
                            <p>Recibe<span class="text-primary">@{{payment.order.order_addresses[0].name}}</span></p>
                            <p>Direccion<span class="text-primary">@{{payment.order.order_addresses[0].address}}</span></p>
                            <p>Ciudad <span class="text-primary">@{{payment.order.order_addresses[0].city}}</span></p>
                            <p>Telefono <span class="text-primary">@{{payment.order.order_addresses[0].phone}}</span></p>
                        </div>

                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="coupon_code right">
                        <h3>Resumen Pago</h3>
                        <div class="coupon_inner">
                            <div class="cart_subtotal" ng-show="hasTransactionCost">
                                <p>Subtotal</p>
                                <p class="cart_amount">@{{payment.subtotal| currency}}</p>
                            </div>
                            <div class="cart_subtotal" ng-hide="hasTransactionCost">
                                <p>Total</p>
                                <p class="cart_amount">@{{payment.subtotal| currency}}</p>
                            </div>
                            <div class="cart_subtotal " ng-show="hasTransactionCost">
                                <p>Transacción</p>
                                <p class="cart_amount">@{{payment.transaction_cost| currency}}</p>
                            </div>
                            <div class="cart_subtotal" ng-show="hasTransactionCost">
                                <p>Total</p>
                                <p class="cart_amount">@{{payment.total| currency}}</p>
                            </div>
                            <div class="checkout_btn">
                                <a href="javascript:;" ng-show="payment.status != 'approved'" ng-click="retryPayment()">Reintentar Pago</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--coupon code area end-->
        <div class="row">
            <div class="col-lg-7 mb--20" ng-controller="CheckoutBillingCtrl" ng-show="paymentActive">
                <div class="coupon_code left">
                    <h3>Selecciona un método de pago</h3>
                    <div class="coupon_inner">
                        @include(config("app.views").'.products.checkoutPaymentMethods')
                    </div>
                </div>
            </div>
        </div>
    </div>     
</div>
<!--shopping cart area end -->

@endsection
