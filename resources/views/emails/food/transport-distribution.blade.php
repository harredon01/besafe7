@component('mail::message')

# Detalles de la orden de compra

@foreach ($data as $route)
@component('mail::panel')

@component('mail::panel')
# Ruta {{ $route->id }}
Proveedor: {{ $route->provider }} 
# Resumen:  
@foreach ($route->totals['keywords'] as $line)
{{ $line }}  
@endforeach 

@foreach ($route->totals['dish'] as $line)
{{ $line }}  
@endforeach  
@endcomponent

@component('mail::panel')
# Distribuido por parada
@foreach ($route->stops as $stop)
## Parada {{ $stop->id }}
@foreach ($stop->totals['keywords'] as $line)
{{ $line }}
@endforeach 
@foreach ($stop->totals['dish'] as $line)
{{ $line }}
@endforeach 
@if (array_key_exists('starter',$stop->totals ))
@foreach ($stop->totals['starter'] as $line)
{{ $line }}
@endforeach 
@endif
@foreach ($stop->deliveries as $delivery)
### {{ $delivery->id }} {{ $delivery->user->firstName }} {{ $delivery->user->lastName }}
@foreach ($delivery->totals['keywords'] as $line)
{{ $line }}
@endforeach 
@foreach ($delivery->totals['dish'] as $line)
{{ $line }}
@endforeach 
@if (array_key_exists('starter',$delivery->totals ))
@foreach ($delivery->totals['starter'] as $line)
{{ $line }}
@endforeach 
@endif
@endforeach 
@endforeach 
@endcomponent
@endcomponent
@endforeach 

Gracias,<br>
{{ config('app.name') }}
@endcomponent
