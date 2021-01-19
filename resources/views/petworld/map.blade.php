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
                    
                    <p>Para saber que productos y servicios podemos ofrecerte necesitamos saber donde los vas a recibir.</p>
                    <p>Si el pin está en tu ubicacion correcta haz click en guardar. De lo contrario arrastra el pin hasta el lugar correcto</p>
                    <button ng-click="saveLocation()" class="btn btn-primary">Guardar</button>
                    <button ng-click="cancel()" class="btn btn-primary">Cancelar</button>
                    <div id="map" data-tap-disabled="true"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/angular-google-maps/2.3.2/angular-google-maps.min.js" async></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCOlc_3d8ygnNCMRzfEpmvSNsYtmbowtYo"></script>
@endsection
