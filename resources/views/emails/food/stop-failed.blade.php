@component('mail::message')

# Hubo un error en la ruta {{$route->id}}

El mensajero que esta entregando esa ruta se llama {{$runnerName}} y su celular es {{$runnerPhone}}

La informacion de la ruta se encuentra a continuacion. 

@component('mail::panel')
@foreach ($route->stops as $stop)
Parada {{ $stop->id }} 
Direccion: {{ $stop->address->address }} 
Tel:{{ $stop->address->phone }}

Entregas
@foreach ($stop->deliveries as $delivery)
Entrega {{ $delivery->id }} usuario {{ $delivery->user->firstName }} {{ $delivery->user->lastName }}
@endforeach

@endforeach
@endcomponent


Gracias,<br>
{{ config('app.name') }}
@endcomponent
