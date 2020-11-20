@component('mail::message')

# Escenario

@foreach ($data as $route)
@component('mail::panel')

# Ruta {{ $route->id }}
Entregas {{ $route->unit }}  
Ingreso Envios {{ number_format($route->unit_price, 2, ',', '.') }}  
Costo Envios {{ number_format($route->unit_cost, 2, ',', '.') }}  
Diferencia {{ number_format($route->unit_price- $route->unit_cost, 2, ',', '.') }}  
@component('mail::button', ['url' => config('app.url'). "/food/build_route_id/".$route->id."/".$route->hash])
Generar
@endcomponent
@endcomponent
@endforeach 

Gracias,<br>
{{ config('app.name') }}
@endcomponent
