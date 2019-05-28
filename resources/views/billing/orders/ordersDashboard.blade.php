@extends('layouts.app')

@section('content')
<div class="container-fluid" ng-controller="OrdersCtrl">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-body">
                    <a href="javascript:;" ng-click="getStoreExport()">Enviar Reporte</a><br/><br/>
                    <div class="replace-address">
                        @include('billing.orders.ordersList')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
