@extends(config("app.views").'.layouts.app')

@section('content')
<div class="container-fluid" ng-controller="OrdersCtrl">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-body">
                    <label for="from">From</label>
                    <input type="text" id="from" ng-model="from" name="from">
                    <label for="to">to</label>
                    <input type="text" id="to" name="to" ng-model="to"><br/><br/>
                    <a href="javascript:;" ng-click="getStoreExport()">Enviar Reporte</a><br/><br/>
                    <div class="replace-address">
                        @include(config("app.views").'.billing.orders.ordersList')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
