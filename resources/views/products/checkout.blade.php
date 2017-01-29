@extends('layouts.app')

@section('content')
<div class="container-fluid" >
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Hola {{$user->firstName}}, selecciona tu direccion de correspondencia

                </div>
                <div class="panel-body">
                    @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <strong>Whoops!</strong> There were some problems with your input.<br><br>
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error}}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <div class="replace-checkout-cart" ng-controller="CheckoutCartCtrl">
                        @include('products.checkoutCart')
                    </div>
                    <div class="address" ng-controller="CheckoutShippingCtrl">
                        <div class="replace-address">
                            @include('user.checkoutAddressList')
                            <a href="javascript:;" ng-click="showAddressForm()">Nueva direccion</a>
                        </div>



                        <div ng-show="addAddress">
                            @include('user.editAddressForm')
                            <a href="javascript:;" ng-click="hideAddressForm()">Cerrar</a>
                        </div>
                        <div class="replace-address" ng-show="shippingAddressSet">
                            
                            @include('products.ShippingMethodsList')
                        </div>

                    </div>

                    <div class="payment-methods" ng-show="shippingCondition" ng-controller="CheckoutBillingCtrl">
                        <div class="replace-address">
                            @include('products.checkoutPaymentMethods')
                        </div>
                        
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>
@endsection
