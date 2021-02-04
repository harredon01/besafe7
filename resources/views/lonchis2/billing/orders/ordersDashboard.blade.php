@extends(config("app.views").'.layouts.app')

@section('content')
<div class="container" ng-controller="OrdersCtrl">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <label for="from">Fromm</label>
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
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="/resources/demos/style.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  
@endsection
