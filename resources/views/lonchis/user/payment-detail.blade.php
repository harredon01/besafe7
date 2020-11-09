@extends(config("app.views").'.layouts.app')

@section('content')

<!-- Cart Page Start -->
<div class="cart_area cart-area-padding sp-inner-page--top" ng-controller="PaymentDetailCtrl">
    <div class="container">
        <div class="page-section-title">
            <h1>SHOPPING CART</h1>
        </div>
        <div class="row">
            <div class="col-12">
                <form action="#" class="">
                    <!-- Cart Table -->
                    <div class="cart-table table-responsive mb--40">
                        <table class="table">
                            <!-- Head Row -->
                            <thead>
                                <tr>
                                    <th class="pro-thumbnail">Imagen</th>
                                    <th class="pro-title">Producto</th>
                                    <th class="pro-price">Precio</th>
                                    <th class="pro-quantity">Cantidad</th>
                                    <th class="pro-subtotal">Total</th>
                                </tr>

                            </thead>
                            <tbody>
                                <!-- Product Row -->
                                <tr ng-repeat="item in payment.order.items">
                                    <td class="pro-thumbnail"><a href="#"><img src="image/product/home-1/product-1.jpg" alt="Product"></a></td>
                                    <td class="pro-title"><a href="#">@{{item.name}}</a></td>
                                    <td class="pro-price"><span>@{{item.price| currency}}</span></td>
                                    <td class="pro-quantity"><span>@{{item.quantity| currency}}</span></td>
                                    <td class="pro-subtotal"><span>@{{item.priceSumConditions| currency}}</span></td>
                                </tr>
                                <!-- Discount Row  -->
                                <tr>
                                    <td colspan="6" class="actions">

                                        <div class="coupon-block">
                                            <div class="coupon-text">
                                                <label for="coupon_code">Coupon:</label>
                                                <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="Coupon code">
                                            </div>
                                            <div class="coupon-btn">
                                                <input type="submit" class="btn-black" name="apply_coupon" value="Apply coupon">
                                            </div>
                                        </div>

                                        <div class="update-block text-right">
                                            <input type="submit" class="btn-black" name="update_cart" value="Update cart">
                                            <input type="hidden" id="_wpnonce" name="_wpnonce" value="05741b501f"><input type="hidden" name="_wp_http_referer"
                                                                                                                         value="/petmark/cart/">
                                        </div>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<div class="cart-section-2 sp-inner-page--bottom">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-12 d-flex">
                <div class="cart-summary">
                    <div class="cart-summary-wrap">
                        <h4><span>Resumen Pago</span></h4>
                        <p>Sub Total <span class="text-primary">@{{payment.subtotal| currency}}</span></p>
                        <p>Transaccion <span class="text-primary">@{{payment.transaction_cost| currency}}</span></p>
                        <h2>Gran Total <span class="text-primary">@{{payment.subtotal| currency}}</span></h2>
                    </div>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="col-lg-6 col-12 d-flex">
                <div class="cart-summary">
                    <div class="cart-summary-wrap">
                        <h4><span>Resumen Pago</span></h4>
                        <p>Sub Total <span class="text-primary">@{{payment.subtotal| currency}}</span></p>
                        <p>Transaccion <span class="text-primary">@{{payment.transaction_cost| currency}}</span></p>
                        <h2>Gran Total <span class="text-primary">@{{payment.subtotal| currency}}</span></h2>
                    </div>
                    <div class="cart-summary-button">
                        <a href="checkout.html" class="checkout-btn c-btn">Checkout</a>
                        <button class="update-btn c-btn">Update Cart</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-7 mb--20" ng-controller="CheckoutBillingCtrl" ng-show="paymentActive">
                <h4 class="checkout-title">Selecciona un método de pago</h4>
                @include(config("app.views").'.products.checkoutPaymentMethods')
            </div>
        </div>
    </div>
</div>
<!-- Cart Page End -->
@endsection
