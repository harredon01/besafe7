@extends('layouts.app')

@section('content')
<div class="container-fluid" ng-controller="UserAddressesCtrl">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Hola {{$user->firstName}}, aca puedes editar tus direcciones. Debes tener una de tipo billing para completar una orden

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
                    <div class="replace-address">
                        @include('user.addressList')
                    </div>


                    <div >
                       
                        @include('user.editAddressForm')
                    
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
