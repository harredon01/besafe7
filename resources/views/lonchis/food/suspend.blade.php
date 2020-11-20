@extends(config("app.views").'.layouts.app')

@section('content')
<div class="container-fluid" ng-controller="ProductsCtrl">
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
                        <ul>
                            @foreach ($data as $route)
                            <li>
                                            <h2>
                                                Ruta {{ $route->id }}
                                            </h2>
                                            <p style="{{ $style['paragraph'] }}">
                                                Entregas {{ $route->unit }}
                                            </p>
                                            <p style="{{ $style['paragraph'] }}">
                                                Ingreso Envios {{ $route->unit_price }}
                                            </p>

                                            <p style="{{ $style['paragraph'] }}">
                                                Costo Envios {{ $route->unit_cost }}
                                            </p>

                                            <p style="{{ $style['paragraph'] }}">
                                                Diferencia {{ $route->unit_price- $route->unit_cost }}
                                            </p>
                                            <p style="{{ $style['paragraph'] }}">
                                                <a href="{{ $route->hash }}">Generar</a>
                                            </p>
                                @foreach ($route->stops as $stop)

                                <table align="center" width="570" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="{{ $fontFamily }}">
                                            Parada {{ $stop->id }} 
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="{{ $fontFamily }}">
                                                Direccion: {{ $stop->address->address }} <br/>
                                                Tel:{{ $stop->address->phone }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="{{ $fontFamily }}">
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
