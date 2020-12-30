@extends(config("app.views").'.layouts.app')

@section('content')
<div class="container" ng-controller="RapigoCtrl">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">               
                
                <div class="panel-heading"></div>
                      

                <div class="panel-body">
                    <h3>Seguimiento Rapigo</h3>
                    <p>Aca puedes ver como va el mensajero con el pedido por entregar</p>
                    <button ng-click="getUpdate()" class="btn btn-primary">Actualizar</button>
                    <div id="map" data-tap-disabled="true"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
