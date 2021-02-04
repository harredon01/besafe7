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
                        <h3 ng-show="delivery">Selecciona fecha de entrega</h3>
                        <div layout-gt-xs="row" ng-show="delivery">
                            
                            <div flex-gt-xs>
                                <h4>Fecha</h4>
                                <md-datepicker ng-model="delivery" md-placeholder="Enter date"
                                               input-aria-describedby="datepicker-description"
                                               input-aria-labelledby="datepicker-header "></md-datepicker>
                                <p style="display: none" id="datepicker-description">
                                    You can use input-aria-describedby to have screen readers provide a more detailed
                                    description of a datepicker or its interactions.
                                </p>
                            </div>

                            <div flex-gt-xs>
                                <h4>Hora</h4>
                                <select ng-model="deliveryTime" name="cc_branch" ng-change="selectTime()"  class="form-control nice-select"  required>
                            <option value="7:00:00">7:00 am</option>
                            <option value="8:00:00">8:00 am</option>
                            <option value="9:00:00">9:00 am</option>
                            <option value="10:00:00">10:00 am</option>
                            <option value="11:00:00">11:00 am</option>
                            <option value="12:00:00">12:00 pm</option>
                            <option value="13:00:00">1:00 pm</option>
                            <option value="14:00:00">2:00 pm</option>
                            <option value="15:00:00">3:00 pm</option>
                            <option value="16:00:00">4:00 pm</option>
                        </select>
                            </div>
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
