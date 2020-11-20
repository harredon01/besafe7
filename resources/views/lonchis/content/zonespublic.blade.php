@extends('layouts.zeappcontr')

@section('content')
<div class="container" ng-controller="ZonesPubCtrl">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="replace-address">
                        @include(config("app.views").'.content.zonesListPublic')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
