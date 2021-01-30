@extends(config("app.views").'.layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-sortablejs@latest/jquery-sortable.js"></script>
<div class="container" ng-controller="RoutesCtrl">
    <div class="row">
        <div class="col-md-12"> 
            <div class="panel panel-default">
                <div class="panel-body">
                    Escenario <br/>
                    <select ng-model="scenario" ng-change="changeScenario()">
                        <option value="simple">Simple</option>
                        <option value="preorganize">Preorganizado</option>
                    </select><br/>
                    <select ng-model="status" ng-change="changeScenario()">
                        <option value="pending">Pending</option>
                        <option value="enqueue">Esperando</option>
                    </select><br/>
                    <div ng-if="status == 'pending'" >

                    </div>
                    <select ng-model="provider" ng-change="changeScenario()">
                        <option value="Rapigo">Rapigo</option>
                        <option value="Basilikum">Basilikum</option>
                    </select><br/>

                    <a href="javascript:;" ng-click="sendReminder()">Enviar Recordatorio</a><br/><br/>
                    <!--a href="javascript:;" ng-click="regenerateDeliveries()">Regenerate Deliveries</a><br/><br/-->
                    <a href="javascript:;" ng-click="regenerateScenarios()">Regenerate Scenarios</a><br/><br/>
                    <a href="javascript:;" ng-click="getTotalShippingCosts()">Get total shipping costs</a><br/><br/>
                    <a href="javascript:;" ng-click="getScenarioEmails()">Get scenario emails</a><br/><br/>
                    <a href="javascript:;" ng-click="getScenarioOrganization()">Get scenario Structure emails</a><br/><br/>
                    <a href="javascript:;" ng-if="status=='enqueue'" ng-click="buildScenarioLogistics()">Build scenario logistics</a><br/><br/>
                    <a href="javascript:;" ng-click="getPurchaseOrder()">Get Purchase Order</a><br/><br/>
                    <a href="javascript:;" ng-click="activateMap()">Activate Map</a><br/><br/>
                    <div class="mapcont" ng-show="mapActive">
                        <div id="map"></div>
                    </div>
                    <a href="javascript:;" ng-click="showAll()">Show All</a><br/><br/>
                    <div class="replace-address">
                        @include(config("app.views").'.food.routesList')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
