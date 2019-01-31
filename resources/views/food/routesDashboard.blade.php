@extends('layouts.app')

@section('content')
<div class="container-fluid" ng-controller="RoutesCtrl">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-body">
                    <a href="javascript:;" ng-click="regenerateScenarios()">Regenerate</a>
                    <div class="replace-address">
                        @include('food.routesList')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
