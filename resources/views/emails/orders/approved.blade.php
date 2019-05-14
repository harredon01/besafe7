@component('mail::message')
# Orden #{{$order->id}} Aprobada 

@component('mail::table')
| Nombre         | Valor          | Cantidad          |Total                        |
| :------------- |---------------:| -----------------:|----------------------------:|
@foreach ($order->items as $item)
| {{$item->name}}|${{number_format($item->price, 2, ',', '.')}}|{{$item->quantity}}|${{number_format($item->priceSumConditions, 2, ',', '.')}}|
@endforeach  
@endcomponent

@component('mail::table')
| Nombre         | Tipo          | Valor            |
| :------------- |--------------:|-----------------:|
@foreach ($order->orderConditions as $item)
| {{$item->name}}|{{$item->value}}|${{number_format($item->total, 2, ',', '.')}} |
@endforeach
|                |     Total      |${{number_format($order->total, 2, ',', '.')}}|     
@endcomponent
@if ($invoice == 'yes')
@component('mail::table')
| La Orden           | Valor            |
|:-----------------|-----------------:|
|Productos         |${{number_format($order->totalCost, 2, ',', '.')}}|
|ImpoConsumo       |${{number_format($order->tax, 2, ',', '.')}}|
|Depositos, menaje y meseros |${{number_format($order->totalDeposit, 2, ',', '.')}}|
|Logistica total |${{number_format($order->totalPlatform, 2, ',', '.')}}|
|Total             |${{number_format($order->total, 2, ',', '.')}}|
@endcomponent
@endif
@component('mail::table')
| Tu Pago #{{$order->payment->id}}         | Valor            |
|:-----------------|-----------------:|
|Subtotal          |${{number_format($order->payment->subtotal, 2, ',', '.')}}|
|Transaccion       |${{number_format($order->payment->transaction_cost, 2, ',', '.')}}|
|Total             |${{number_format($order->payment->total, 2, ',', '.')}}|
@endcomponent

@component('mail::table')
| Tus datos        | Valor            |
|:-----------------|-----------------:|
|Nombre:        |{{$user->firstName}} {{$user->lastName}}|
|Dirección:       |{{$shipping->address}}|
|Tel:       |{{$shipping->phone}}|
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
