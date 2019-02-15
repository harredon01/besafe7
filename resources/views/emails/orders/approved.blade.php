@component('mail::message')
# Introduction

Orden #{{$order->id}}
Estado: {{$order->status}}
@component('mail::table')
| Nombre         | Valor          | Cantidad          |Total                        |
| :------------- |---------------:| -----------------:|----------------------------:|
@foreach ($order->items as $item)
| {{$item->name}}|{{number_format($item->price, 2, ',', '.')}}|{{$item->quantity}}|{{number_format($item->priceSumConditions, 2, ',', '.')}}|
@endforeach  
@endcomponent

@component('mail::table')
| Nombre         | Tipo          | Valor            |
| :------------- |--------------:|-----------------:|
@foreach ($order->orderConditions as $item)
| {{$item->name}}|{{$item->value}}|{{number_format($item->total, 2, ',', '.')}} |
@endforeach
|                |     Total      |{{number_format($order->total, 2, ',', '.')}}|     
@endcomponent

@component('mail::button', ['url' => ''])
Button Text
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
