@extends(config("app.views").'.layouts.app')

@section('content')


<main id="content" class="page-section sp-inner-page checkout-area-padding space-db--20">
    <div class="container">
        <div class="row">
            <div class="col-12">

                <!-- Checkout Form s-->
                <div class="checkout-form">
                    <div class="row row-40" >

                        <div class="col-lg-7 mb--20" ng-controller="CheckoutShippingCtrl" ng-show="visible && !paymentActive" id="checkout-shipping">
                            <!-- Shipping Address -->
                            <div class="checkout-quick-box" ng-show="shippingAddressSet">
                                <p><i class="far fa-sticky-note"></i> <a href="javascript:;" ng-click="changeAddress()">
                                        Cambiar dirección</a></p>
                            </div>
                            <div ng-show="!shippingAddressSet && !addAddress">
                                @include(config("app.views").'.user.checkoutAddressList')
                            </div>
                            <span ng-show="!shippingAddressSet" style="color:#56a700;float:right">
                                <a href="javascript:;" ng-click="showAddressForm()">Nueva direccion</a>
                            </span>
                            @include(config("app.views").'.user.editAddressFormCheckout')
                            <div class="checkout-quick-box" ng-show="shippingConditionSet">
                                <p><i class="far fa-sticky-note"></i> <a href="javascript:;" ng-click="changeShipping()">
                                        Cambiar Metodo de envio</a></p>
                            </div>



                            <!-- Shipping Methods -->
                            <div ng-show="shippingAddressSet && !shippingConditionSet" style="color:black;font-weight: bold">
                                <h4 class="checkout-title">Selecciona un método de envío</h4>
                                <p>@{{addressSet.address}}, @{{addressSet.city}} </p>
                                @include(config("app.views").'.products.ShippingMethodsList')
                            </div>
                            <h4 ng-show="shippingConditionSet" class="checkout-title">Listo! Puedes ir a pagar</h4>
                        </div>
                        <div class="col-lg-7 mb--20" ng-controller="CheckoutBillingCtrl" ng-show="paymentActive">
                            <h4 class="checkout-title">Selecciona un método de pago</h4>
                            @include(config("app.views").'.products.checkoutPaymentMethods')
                        </div>

                        <div class="col-lg-5">
                            <div class="row" ng-controller="CheckoutCartCtrl" id="checkout-cart">

                                <!-- Cart Total -->
                                @include(config("app.views").'.products.checkoutCart')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
