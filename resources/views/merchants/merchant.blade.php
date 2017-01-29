@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Home</div>

                <div class="panel-body">
                    You are logged in!
                    <br/>
                    
                    <table>

  
                            <tr>
                                <td><label>Nombre</label>{{ $merchant->name }}</td>
                                <td><label>Email</label>{{ $merchant->email }}</td>
                                
                            </tr>
                            <tr>
                                <td><label>Teléfono</label>{{ $merchant->telephone }}</td>
                                <td><label>Direccion</label>{{ $merchant->address }}</td>
                                </tr>
                            <tr>
                                <td><label>Descripcion</label>{{ $merchant->description }}</td>
                                <td><label>Pedido Minimo</label>{{ $merchant->minimum }}</td>



                            </tr>

     <tr>
                                <td><label>Tiempo de entrega</label>{{ $merchant->delivery_time }}</td>
                                <td><label>precio del domicilio</label>{{ $merchant->delivery_price }}</td>



                            </tr>
                             <tr>
                                <td><label>Estado</label>{{ $merchant->status }}</td>
                                <td><label>Activo desde</label>{{ $merchant->created_at }}</td>



                            </tr>



                        </table>

                    <a href="merchantProducts/{{ $merchant->id }}" class="editar">Editar</a>
                    
                </div>
            </div>
            
             <div class="panel panel-default">
                <div class="panel-heading">Ordenes</div>

                <div class="panel-body">@if (count($merchant->orders) > 0)
                    <div>
                        Listed Merchants<br><br>
                        <table>
                            <tr>
                                <th>
                                    Id
                                </th>

                                <th>Estado</th>
                                <th>Comentarios</th>
                                <th>total</th>
                                <th>fecha</th>
                                <th>Ver Detalle</th>
                            </tr>

                            @foreach ($merchant->orders as $order)
                            <tr>
                                <td>{{ $order->id }}</td>
                                <td>{{ $order->status }}</td>
                                <td>{{ $order->comments }}</td>
                                <td>{{ $order->total }}</td>
                                <td>{{ $order->updated_at }}</td>
                                <td><a href="order/{{ $order->id }}" class="editar">Ver</a></td>



                            </tr>

                            @endforeach



                        </table>


                    </div>

                    @endif</div>
             </div>
        </div>
    </div>
</div>
@endsection
