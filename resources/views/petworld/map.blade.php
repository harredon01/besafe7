@extends(config("app.views").'.layouts.app')

@section('content')
<div class="container" ng-controller="MapLocationCtrl">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">               
                
                <div class="panel-heading"><h3>Donde está tu peludito?</h3></div>
                      

                <div class="panel-body">
                    
                    <div>
                        <img style="display: block;margin:0 auto;" src="https://gohife.s3.us-east-2.amazonaws.com/public/dog-map2.jpg"/>
                    </div>
                    <p >Links rápidos: <a href="javascript:;" class="text-primary" ng-click="selectCity(4.6707198124020675, -74.06671125317112)">Bogota</a>, <a href="javascript:;" class="text-primary" ng-click="selectCity(6.249380169398565, -75.5727346053754)">Medellin</a>, <a href="javascript:;" class="text-primary" ng-click="selectCity(3.423507212808105, -76.52051677752884)">Cali</a>
                    , <a href="javascript:;" class="text-primary" ng-click="selectCity(11.236660039676785, -74.19140938524001)">Santa Marta</a>, <a href="javascript:;" class="text-primary" ng-click="selectCity(10.42839203353642, -75.51813914198655)">Cartagena</a></p>
                    <p><b>Tu dirrección de entrega</b></p>
                    <input type="text" class="form-control" placeholder="Direccion de entrega" id="pac-input" ng-model="addressContainer"/>
                    <p>Verifica el pin y la direccion. si no estan correctos puedes editar la direccion y arrastrar el pin a su lugar correcto. </p>
                    <button ng-click="saveLocation()" class="btn" style="background-color: #56a700;color: white;border-color: #56a700;height: 26px;float: right;width: 80px;">Continuar</button>
                    <button ng-click="cancel()" class="btn" style="background-color: #56a700;color: white;border-color: #56a700;height: 26px;width: 80px;float: right;margin-right: 10px;margin-bottom: 10px;">Cancelar</button>
                    
                    <div id="map" data-tap-disabled="true"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/angular-google-maps/2.3.2/angular-google-maps.min.js" async></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCOlc_3d8ygnNCMRzfEpmvSNsYtmbowtYo&libraries=places"></script>
@endsection
