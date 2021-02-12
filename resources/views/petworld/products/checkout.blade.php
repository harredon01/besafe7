@extends(config("app.views").'.layouts.app')

@section('content')


<main id="content" class="page-section sp-inner-page checkout-area-padding space-db--20">
    <div class="container">
        <div class="row">
            <div class="col-12">

                <!-- Checkout Form s-->
                <div class="checkout-form">
                    <div class="row row-40" >
                        <div class="col-lg-7 mb--20">
                            <div  ng-controller="CheckoutShippingCtrl" ng-show="visible && !paymentActive" id="checkout-shipping">
                                <!-- Shipping Address -->
                                <div class="checkout-quick-box" ng-show="shippingAddressSet">
                                    <p><i class="far fa-sticky-note"></i> <a href="javascript:;" ng-click="changeAddress()">
                                            Cambiar dirección</a></p>
                                </div>
                                <div ng-show="!shippingAddressSet && !addAddress">
                                    @include(config("app.views").'.user.checkoutAddressList')
                                </div> 
                                <span ng-show="!shippingAddressSet" style="color:#56a700;float:right">
                                    <a href="javascript:;" ng-click="newAddress()">Nueva direccion</a>
                                </span>
                                <div style="clear:both"></div>
                                @include(config("app.views").'.user.editAddressFormCheckout')
                                <!-- Shipping Methods -->
                                <div ng-show="shippingAddressSet" id="checkout-shipping-methods" style="color:black;font-weight: bold">
                                    <h4>Dirección de envío</h4>
                                    <p><strong>@{{addressSet.address}}, @{{addressSet.city}}</strong> </p>
                                    <p><strong>@{{addressSet.notes}}</strong></p>
                                    <h4 class="checkout-title">Envío</h4>

                                    <p ng-show="expectedProviders > 0">Esperando respuesta proveedores logisticos <img width="30" src="/images/loader.gif"/></p>
                                    @include(config("app.views").'.products.ShippingMethodsList')
                                </div>
                            </div>
                            <br/>
                            <div  ng-controller="CheckoutBookCtrl" ng-show="visibleBooking" id="checkout-booking"> 
                                <h4 class="text-black">Tus citas</h4>
                                @include(config("app.views").'.products.BookingList')
                                <br/>
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

                            <h4 class="checkout-title" ng-show="shippingConditionSet && bookingSet && !paymentActive || isDigital && bookingSet && !paymentActive">Listo! Puedes ir a pagar</h4>
                            <div ng-controller="CheckoutBillingCtrl" ng-show="paymentActive" id="checkout-payment">
                                <h4 class="checkout-title" ng-hide="showResult">Selecciona un método de pago</h4>
                                @include(config("app.views").'.products.checkoutPaymentMethods')
                            </div>
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
</div>
</main>
@endsection
