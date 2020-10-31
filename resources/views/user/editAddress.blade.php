@extends('layouts.app')

@section('content')
<div class="container-fluid" ng-controller="AddressesCtrl">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Hola {{$user->firstName}}, aca puedes editar tus direcciones.

                </div>
                <div class="panel-body">
                    <a href="javascript:;" ng-click="newAddress()">New address</a>
                    <div class="replace-address" ng-hide="editAddress">
                        @include('user.addressList')
                    </div>
                    <div ng-show="editAddress">
                        @include('user.editAddressForm')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
