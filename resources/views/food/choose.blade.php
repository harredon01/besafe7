@extends('layouts.app')

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
                    @if (count($data) > 0)
                    <div >
                        <p>
                            <a href="/food/build_complete_scenario/preorganize-1/{{$result['scenario_hash']}}">Seleccionar</a>  <br>
                        <a href="/food/build_scenario_positive/preorganize-1/{{$result['scenario_hash']}}">Activar los positivios</a>  <br>
                        </p>
                        <ul>
                            @foreach ($data as $route)
                            <li>
                                            <h2>
                                                Ruta {{ $route->id }}
                                            </h2>
                                            <p>
                                                Entregas {{ $route->unit }}
                                            </p>
                                            <p>
                                                Ingreso Envios ${{ number_format($route->unit_price, 2, ',', '.') }}
                                            </p>

                                            <p>
                                                Costo Envios ${{ number_format($route->unit_cost, 2, ',', '.') }}
                                            </p>

                                            <p>
                                                Diferencia ${{ number_format($route->unit_price- $route->unit_cost, 2, ',', '.') }}
                                            </p>
                                            <p>
                                                <a href="/food/build_route_id/{{ $route->id }}/{{ $route->hash }}">Generar</a>
                                            </p>
                                @foreach ($route->stops as $stop)

                                <table align="center" width="570" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td>
                                            Parada {{ $stop->id }} 
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                                Direccion: {{ $stop->address->address }} <br/>
                                                Tel:{{ $stop->address->phone }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                                {!!$stop->region_name!!}
                                        </td>
                                    </tr>
                                </table>
                                @endforeach
                                </li>
                                @endforeach
                        </ul>
                    </div>

                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
