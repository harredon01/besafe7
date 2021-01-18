@extends(config("app.views").'layouts.app')

@section('content')
<div class="container" ng-controller="MapCtrl">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">               
                @if (isset($following))


                <div class="panel-heading">Siguiendo a {{ $following->firstName }} {{ $following->lastName }}</div>

                <div id="map" data-tap-disabled="true"></div>
                <div class="panel-body">

                </div>
                @else 
                <div class="panel-heading">No hay nadie compartiendo con ese código


                    <div class="panel-body">

                    </div>

                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
    @endsection
