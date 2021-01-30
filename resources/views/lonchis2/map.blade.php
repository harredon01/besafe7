@extends(config("app.views").'.layouts.app')

@section('content')
<div class="container" ng-controller="MapLocationCtrl">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">               
                
                <div class="panel-heading"><h3>Podemos ubicarte?</h3></div>
                      

                <div class="panel-body">
                    
                    <div style="display:none">
                        <img style="display: block;margin:0 auto;" src="https://gohife.s3.us-east-2.amazonaws.com/public/dog-map2.jpg"/>
                    </div>
                    
                    <p>Para saber que productos y servicios podemos ofrecerte necesitamos saber donde los vas a recibir.</p>
                    <p>Si el pin está en tu ubicacion correcta haz click en guardar. De lo contrario arrastra el pin hasta el lugar correcto</p>
                    <p >Links rápidos: <a href="javascript:;" class="text-primary" ng-click="selectCity(4.6707198124020675, -74.06671125317112)">Bogota</a>, <a href="javascript:;" class="text-primary" ng-click="selectCity(6.249380169398565, -75.5727346053754)">Medellin</a>, <a href="javascript:;" class="text-primary" ng-click="selectCity(3.423507212808105, -76.52051677752884)">Cali</a>
                    , <a href="javascript:;" class="text-primary" ng-click="selectCity(11.236660039676785, -74.19140938524001)">Santa Marta</a>, <a href="javascript:;" class="text-primary" ng-click="selectCity(10.42839203353642, -75.51813914198655)">Cartagena</a></p>
                    <button ng-click="saveLocation()" class="btn btn-primary">Guardar</button>
                    <button ng-click="cancel()" class="btn btn-primary">Cancelar</button>
                    <div id="map" style="height: 500px" data-tap-disabled="true"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/angular-google-maps/2.3.2/angular-google-maps.min.js" async></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCOlc_3d8ygnNCMRzfEpmvSNsYtmbowtYo"></script>
@endsection
