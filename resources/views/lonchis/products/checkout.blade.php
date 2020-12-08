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
                            <div ng-show="shippingAddressSet && !shippingConditionSet" id="checkout-shipping-methods" style="color:black;font-weight: bold">
                                <h4 class="checkout-title">Selecciona un método de envío</h4>
                                <p>@{{addressSet.address}}, @{{addressSet.city}} </p>
                                <p ng-show="expectedProviders>0">Esperando respuesta proveedores logisticos <img width="30" src="/images/loader.gif"/></p>
                                @include(config("app.views").'.products.ShippingMethodsList')
                            </div>
                        </div>
                        <div class="col-lg-7 mb--20" ng-controller="CheckoutBookCtrl" ng-show="visibleBooking && !paymentActive" id="checkout-booking">
                            <h4 class="checkout-title" ng-show="appointments.length>0">Programa tus citas</h4>
                                @include(config("app.views").'.products.BookingList')
                        </div>
                        <div class="col-lg-7 mb--20" ng-show="shippingConditionSet && bookingSet && !paymentActive || isDigital && bookingSet && !paymentActive">
                            <h4 class="checkout-title">Listo! Puedes ir a pagar</h4>
                        </div>
                        <div class="col-lg-7 mb--20" ng-controller="CheckoutBillingCtrl" ng-show="paymentActive" id="checkout-payment">
                            <h4 class="checkout-title" ng-hide="showResult">Selecciona un método de pago</h4>
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
