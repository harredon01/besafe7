@extends(config("app.views").'.layouts.app')

@section('content')
<div class="container-fluid" >
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Resumen de tu orden
                </div>
                <div class="panel-body">
                    <div class="replace-checkout-cart" ng-controller="CheckoutCartCtrl">
                        @include(config("app.views").'.products.checkoutCart')
                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                Ingresa un cupon
                                <div>
                                    <input type="text" ng-model="coupon" style="float:left;width:70%" class="form-control" name="coupon" required>
                                    <button ng-click="setCoupon()" style="float:left" class="btn btn-primary">Save</button>
                                </div>
                            </div>
                        </div>
                        <button ng-click="prepareOrder()" style="float:left" ng-show="isDigital" class="btn btn-primary">Pagar</button>
                    </div>
                </div>
            </div>
            <div class="panel panel-default" ng-controller="CheckoutShippingCtrl" ng-show="visible">
                <div class="panel-heading">Direccion de envío
                </div>
                <div class="panel-body">
                    <div class="address" >
                        <div class="replace-address">
                            <div ng-show="!shippingAddressSet && !addAddress">
                                @include(config("app.views").'.user.checkoutAddressList')
                            </div>
                            <a href="javascript:;" ng-show="!shippingAddressSet" ng-click="showAddressForm()">Nueva direccion</a>
                        </div>
                        <div ng-show="addAddress">
                            @include(config("app.views").'.user.editAddressForm')
                            <a href="javascript:;" ng-click="hideAddressForm()">Cerrar</a>
                        </div>
                        <div class="replace-address" ng-show="shippingAddressSet">
                            <p>@{{addressSet.address}}, @{{addressSet.city}} </p>
                            @include(config("app.views").'.products.ShippingMethodsList')
                        </div>
                        <button ng-click="prepareOrder()" style="float:left" ng-show="shippingConditionSet" class="btn btn-primary">Pagar</button>
                    </div>
                </div>
            </div>

            <div class="panel panel-default" ng-show="paymentActive" ng-controller="CheckoutBillingCtrl" id="payment-methods">
                <div class="panel-heading">Pago
                </div>
                <div class="panel-body">
                    <div class="payment-methods">
                        <div class="replace-address">
                            @include(config("app.views").'.products.checkoutPaymentMethods')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
