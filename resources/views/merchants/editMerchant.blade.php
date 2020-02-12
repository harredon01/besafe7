@extends('layouts.app')

@section('content')
<div class="container-fluid" ng-controller="MerchantsCtrl">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Hola {{$user->firstName}}, aca puedes editar tus negocios

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
                        @include('merchants.merchantsList')
                    </div>


                    <div >
                       
                        @include('merchants.editMerchantForm')
                    
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
