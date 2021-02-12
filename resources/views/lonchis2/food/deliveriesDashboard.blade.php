@extends(config("app.views").'.layouts.app')

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/angular-google-maps/2.3.2/angular-google-maps.min.js" async></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCOlc_3d8ygnNCMRzfEpmvSNsYtmbowtYo"></script> 
<section class="main_content_area" ng-controller="UserProfileCtrl">
    <div class="container">   
        <div class="account_dashboard">
            <div class="row">
                @include(config("app.views").'.user.account_menu')
                <div class="col-sm-12 col-md-9 col-lg-9">
                    <!-- Tab panes -->
                    <div class="tab-content dashboard_content">
                        <div class="tab-pane fade  show active" id="dashboard">
                            <h3>Entregas</h3>
                            <div class="login">
                                <div class="login_form_container">
                                    <div class="account_login_form">
                                        <div ng-controller="DeliveriesCtrl">

                                            <select ng-model="status" ng-change="changeScenario()">
                                                <option value="pending">Pending</option>
                                                <option value="enqueue">Esperando</option>
                                            </select><br/>
                                            <select ng-model="provider" ng-change="changeScenario()">
                                                <option value="Rapigo">Rapigo</option>
                                                <option value="Basilikum">Basilikum</option>
                                            </select><br/>
                                            <input type="text" id="from" ng-model="from" name="from">
                                            <br/><a href="javascript:;" ng-click="updateDels()">Actualizar</a><br/><br/><br/>
                                            <a href="javascript:;" ng-click="sendReminder()">Enviar Recordatorio</a><br/><br/>
                                            <a href="javascript:;" ng-click="sendNewsletter()">Enviar Newsletter</a><br/><br/>
                                            <!--a href="javascript:;" ng-click="regenerateDeliveries()">Regenerate Deliveries</a><br/><br/-->
                                            <a href="javascript:;" ng-click="getPurchaseOrder()">Get Purchase Order</a><br/><br/>
                                            <a href="javascript:;" ng-click="activateMap()">Activate Map</a><br/><br/>
                                            <div class="mapcont" ng-show="mapActive">
                                                <div id="map"></div>
                                            </div>
                                            <a href="javascript:;" ng-click="showAll()">Show All</a><br/><br/> 
                                            <div class="replace-address">
                                                @include(config("app.views").'.food.deliveriesList')
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>  
    </div>        	
</section>	

@endsection