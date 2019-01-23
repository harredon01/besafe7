@component('mail::message')
# Introduction

Orden #{{$order->id}}
Estado: {{$order->status}}
@component('mail::table')
| Nombre         | Valor          | Cantidad          |Total                        |
| :------------- |---------------:| -----------------:|----------------------------:|
@foreach ($order->items as $item)
| {{$item->name}}|{{$item->price}}|{{$item->quantity}}|{{$item->priceSumConditions}}|
@endforeach  
@endcomponent

@component('mail::table')
| Nombre         | Tipo          | Valor            |
| :------------- |--------------:|-----------------:|
@foreach ($order->orderConditions as $item)
| {{$item->name}}|{{$item->value}}|{{$item->total}} |
@endforeach
|                |     Total      |{{$order->total}}|     
@endcomponent

@component('mail::button', ['url' => ''])
Button Text
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
