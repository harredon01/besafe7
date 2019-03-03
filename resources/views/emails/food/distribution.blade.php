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
@foreach ($route->deliveries as $delivery)
## {{ $delivery->id }} {{ $delivery->user->firstName }} {{ $delivery->user->lastName }}
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
@endcomponent
@endcomponent
@endforeach 

Gracias,<br>
{{ config('app.name') }}
@endcomponent
