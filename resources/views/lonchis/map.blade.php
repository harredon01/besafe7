@extends(config("app.views").'.layouts.app')

@section('content')
<div class="container" ng-controller="MapLocationCtrl">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">               
                
                <div class="panel-heading"></div>
                      

                <div class="panel-body">
                    <h3>En donde te encuentras?</h3>
                    <p>Tu ubicacion se usa para determinar si te encuentras en cobertura y mostrarte los resultados mas cerca a ti primero. </p>
                    <p>Mueve el pin o haz click en donde te encuentras para continuar</p>
                    <p style="display:none">@{{error}}</p>
                    <button ng-click="saveLocation()" class="btn btn-primary">Guardar</button>
                    <button ng-click="cancel()" class="btn btn-primary">Cancelar</button>
                    <div id="map" data-tap-disabled="true"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
