@extends('layouts.app')

@section('content')
<div class="container-fluid" ng-controller="DeliveriesCtrl">
    <div class="row">
        <div class="col-md-8 col-md-offset-2"> 
            <div class="panel panel-default">
                <div class="panel-body">
                    <select ng-model="status" ng-change="changeScenario()">
                        <option value="pending">Pending</option>
                        <option value="enqueue">Esperando</option>
                    </select><br/>
                    <select ng-model="provider" ng-change="changeScenario()">
                        <option value="Rapigo">Rapigo</option>
                        <option value="Basilikum">Basilikum</option>
                    </select><br/>

                    <a href="javascript:;" ng-click="sendReminder()">Enviar Recordatorio</a><br/><br/>
                    <!--a href="javascript:;" ng-click="regenerateDeliveries()">Regenerate Deliveries</a><br/><br/-->
                    <a href="javascript:;" ng-click="getPurchaseOrder()">Get Purchase Order</a><br/><br/>
                    <a href="javascript:;" ng-click="activateMap()">Activate Map</a><br/><br/>
                    <div class="mapcont" ng-show="mapActive">
                        <div id="map"></div>
                    </div>
                    <a href="javascript:;" ng-click="showAll()">Show All</a><br/><br/>
                    <div class="replace-address">
                        @include('food.deliveriesList')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
