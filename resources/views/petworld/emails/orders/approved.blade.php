@component('mail::message')
# Orden #{{$order->id}} Aprobada 

@component('mail::table')
| Nombre         | Valor          | Cantidad          |Total                        |
| :------------- |---------------:| -----------------:|----------------------------:|
@foreach ($order->items as $item)
| {{$item->name}}|${{number_format($item->price, 2, ',', '.')}}|{{$item->quantity}}|${{number_format($item->priceSumConditions, 2, ',', '.')}}|
@if (isset($item->attributes['data']))
@foreach ($item->attributes['data'] as $question)
|{{$question['name']}}:{{$question['value']}}||||
@endforeach  
@endif
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
@if ($shipping)
|Dirección:       |{{$shipping->address}}|
@endif
|Tel:       |{{$user->cellphone}}|
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
