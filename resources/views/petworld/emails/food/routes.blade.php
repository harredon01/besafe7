@component('mail::message')

# Detalles de la orden de compra

@foreach ($data as $route)
@component('mail::panel')

@component('mail::panel')
# Ruta {{ $route->id }}

@foreach ($route->totals['keywords'] as $line)
{{ $line }}  
@endforeach 

@foreach ($route->totals['dish'] as $line)
{{ $line }}  
@endforeach  
@endcomponent

@component('mail::panel')
@foreach ($route->stops as $stop)
#Parada {{ $stop->id }} 
Direccion: {{ $stop->address->address }}
Tel:{{ $stop->address->phone }}
{!!$stop->region_name!!}
@foreach ($stop->totals['keywords'] as $line)
{{ $line }}
@endforeach 
@foreach ($stop->totals['dish'] as $line)
{{ $line }}
@endforeach 
@foreach ($stop->deliveries as $delivery)
#Parada {{ $stop->id }} 

@endforeach 
@endcomponent


@endcomponent
@endforeach 

Gracias,<br>
{{ config('app.name') }}
@endcomponent
