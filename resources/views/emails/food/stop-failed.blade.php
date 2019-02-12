@component('mail::message')

# Hubo un error en la ruta {{$stop->route_id}}

El mensajero que esta entregando esa ruta se llama {{$runnerName}} y su celular es {{$runnerPhone}}

La informacion de la parada que fracaso se encuentra a continuacion. 

@component('mail::panel')
Parada {{ $stop->id }} 
Direccion: {{ $stop->address->address }} 
Tel:{{ $stop->address->phone }}

Las entregas de esta parada son:  
@foreach ($stop->deliveries as $delivery)
Entrega {{ $delivery->id }} usuario {{ $delivery->user->firstName }} {{ $delivery->user->lastName }}  
@component('mail::button', ['url' =>config('app.url'). "/food/delete_deposit/".$delivery->user->id."/".$delivery->user->activationHash])
Usar Deposito
@endcomponent

@if ($delivery->user->lunchHash)
@component('mail::button', ['url' =>config('app.url'). "/food/delete_last_lunch/".$delivery->user->id."/".$delivery->user->activationHash])
Usar almuerzo
@endcomponent
@else
@endif
@endforeach

@endcomponent


Gracias,<br>
{{ config('app.name') }}
@endcomponent
