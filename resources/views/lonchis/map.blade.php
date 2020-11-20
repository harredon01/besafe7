@extends(config("app.views").'.layouts.app')

@section('content')
<div class="container" ng-controller="MapLocationCtrl">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">               
                
                <div class="panel-heading"></div>
                      

                <div class="panel-body">
                    <button ng-click="saveLocation()" class="btn btn-primary">Guardar</button>
                    <button ng-click="cancel()" class="btn btn-primary">Cancelar</button>
                    <div id="map" data-tap-disabled="true"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
