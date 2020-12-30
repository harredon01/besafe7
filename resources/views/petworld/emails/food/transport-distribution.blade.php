@component('mail::message')

# Detalles de la orden de compra

@foreach ($data as $route)
@component('mail::panel')

@component('mail::panel')
# Ruta {{ $route->id }}
Proveedor: {{ $route->provider }} 
# Resumen:  
@endcomponent

@component('mail::panel')
# Distribuido por parada
@foreach ($route->stops as $stop)
## Parada {{ $stop->id }}
Direccion: {{ $stop->address->address }}
{{ $stop->address->notes }}  
Telefono: {{ $stop->address->phone }}

@foreach ($stop->deliveries as $delivery)
{{ $delivery->id }} {{ $delivery->user->firstName }} {{ $delivery->user->lastName }}
@endforeach 

@endforeach 
@endcomponent
@endcomponent
@endforeach 

Gracias,<br>
{{ config('app.name') }}
@endcomponent
