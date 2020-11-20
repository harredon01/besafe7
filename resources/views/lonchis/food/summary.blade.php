@extends(config("app.views").'.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Products

                </div>
                <div class="panel-body">
                    @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <strong>Whoops!</strong> There were some problems with your input.<br><br>
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error}}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <h1>
                        Resumen de resultados sector alrededor del polygono {{$polygon_id}}
                    </h1>

                    <p>
                        Resultado mas optimo: {{$data['winner']}}
                    </p>
                    <p>
                        <a target="_blank" href="/food/regenerate_scenarios/{{$polygon_id}}/qweqwe">Regenerar Simulacion</a>
                    </p>
                    <p>
                        <a target="_blank" href="/food/regenerate_deliveries">Regenerar Entregas de mentira</a>
                    </p>

                    <p>
                        Resumen Escenario Pre: <br>
                        Estado: {{$data['resultsPre']['status']}}<br>
                        Total Costo: ${{ number_format($data['resultsPre']['ShippingCostEstimate'], 2, ',', '.')}}<br>
                        Ingreso Comision: ${{ number_format($data['resultsPre']['hoov_income'], 2, ',', '.')}}<br>
                        Ingreso Envio: ${{ number_format($data['resultsPre']['shipping_income'], 2, ',', '.')}}<br>
                        Total Ingreso: ${{ number_format($data['resultsPre']['total_income'], 2, ',', '.')}}<br>
                        Total Profit: ${{ number_format($data['resultsPre']['day_profit'], 2, ',', '.')}}<br>
                        Numero de rutas: {{$data['resultsPre']['routes']}}<br>
                        Numero de numero de almuerzos: {{$data['resultsPre']['lunches']}}<br>
                        Promedio almuerzos por ruta: {{$data['resultsPre']['lunch_route']}}<br>
                        <a target="_blank" href="/food/build_complete_scenario/simple-1/{{$data['resultsPre']['scenario_hash']}}">Seleccionar</a> <br>
                        <a target="_blank" href="/food/build_scenario_positive/simple-1/{{$data['resultsSimple']['scenario_hash']}}">Activar los positivios</a>  <br>
                        <a target="_blank" href="/food/get_scenario_structure/simple-1/{{$polygon_id}}">Ver</a>
                    </p>
                    <p>
                        Resumen Escenario Pre: <br>
                        Estado: {{$data['resultsSimple']['status']}}<br>
                        Total Costo: ${{ number_format($data['resultsSimple']['ShippingCostEstimate'], 2, ',', '.')}}<br>
                        Ingreso Comision: ${{ number_format($data['resultsSimple']['hoov_income'], 2, ',', '.')}}<br>
                        Ingreso Envio: ${{ number_format($data['resultsSimple']['shipping_income'], 2, ',', '.')}}<br>
                        Total Ingreso: ${{ number_format($data['resultsSimple']['total_income'], 2, ',', '.')}}<br>
                        Total Profit: ${{ number_format($data['resultsSimple']['day_profit'], 2, ',', '.')}}<br>
                        Numero de rutas: {{$data['resultsSimple']['routes']}}<br>
                        Numero de numero de almuerzos: {{$data['resultsSimple']['lunches']}}<br>
                        Promedio almuerzos por ruta: {{$data['resultsSimple']['lunch_route']}}<br>
                        <a target="_blank" href="/food/build_complete_scenario/preorganize-1/{{$data['resultsSimple']['scenario_hash']}}">Seleccionar</a>  <br>
                        <a target="_blank" href="/food/build_scenario_positive/preorganize-1/{{$data['resultsSimple']['scenario_hash']}}">Activar los positivios</a>  <br>
                        <a target="_blank" href="/food/get_scenario_structure/preorganize-1/{{$polygon_id}}">Ver</a>
                    </p>

                    <!-- Salutation -->
                    <p>
                        Gracias,<br>{{ config('app.name')}}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
