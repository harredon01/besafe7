@extends(config("app.views").'.layouts.app')

@section('content')
<style>
    .form-group{
        margin-top:8px
    }
</style>
<!--Checkout page section-->
<div class="Checkout_section mt-70">
    <div class="container">
        <div class="checkout_form">
            <div class="row">
                <div class="col-lg-6 col-md-6" >
                    <div  ng-controller="CheckoutShippingCtrl" ng-show="visible && !paymentActive" id="checkout-shipping">
                        <div class="user-actions" ng-show="shippingAddressSet">
                            <h3> 
                                <i class="fa fa-file-o" aria-hidden="true"></i>
                                Cambiar dirección
                                <a class="Returning" href="javascript:;" ng-click="changeAddress()" aria-expanded="true">Click aquí</a>     
                            </h3>
                        </div> 
                        <div ng-show="!shippingAddressSet && !addAddress">
                            @include(config("app.views").'.user.checkoutAddressList')
                        </div> 
                        <span ng-show="!shippingAddressSet && !addAddress" style="float:right">
                            <a href="javascript:;" class="text-primary" ng-click="newAddress()">Nueva direccion</a>
                        </span>
                        <div style="clear:both"></div>
                        @include(config("app.views").'.user.editAddressFormCheckout')
                        <!-- Shipping Methods -->
                        <div ng-show="shippingAddressSet" id="checkout-shipping-methods" style="color:black;font-weight: bold">
                            <h4>Dirección de envío</h4>
                            <p><strong>@{{addressSet.address}}, @{{addressSet.city}}</strong> </p>
                            <p><strong>@{{addressSet.notes}}</strong></p>
                            <h4 class="checkout-title" ng-show="shipping.legth > 0">Envío</h4>

                            <p ng-show="expectedProviders > 0">Esperando respuesta proveedores logisticos <img width="30" src="/images/loader.gif"/></p>
                            @include(config("app.views").'.products.ShippingMethodsList')
                        </div>
                    </div>
                    <h4 class="checkout-title text-primary" ng-show="shippingConditionSet && bookingSet && !paymentActive || isDigital && bookingSet && !paymentActive">Listo! Puedes ir a pagar</h4>
                    <div ng-controller="CheckoutBillingCtrl" ng-show="paymentActive" id="checkout-payment">
                        <h3 ng-hide="showResult">Selecciona un método de pago</h3>
                        @include(config("app.views").'.products.checkoutPaymentMethods')
                    </div>
                </div>
                @include(config("app.views").'.products.checkoutCart')
            </div>
            <div class="row">


            </div> 
        </div> 
    </div>       
</div>
</div>
@endsection
