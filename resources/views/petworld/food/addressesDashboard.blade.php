@extends(config("app.views").'.layouts.app')

@section('content')
<div class="container-fluid" ng-controller="FoodAddressesCtrl">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-body">
                    <a href="javascript:;" ng-click="showAll()">Show All</a><br/><br/>
                    <div class="replace-address">
                        @include(config("app.views").'.food.addressesList')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
