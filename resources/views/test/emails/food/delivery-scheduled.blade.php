@component('mail::message')

# Almuerzo programado
Tu almuerzo quedo programado para entrega el {{ $data['date'] }}   con las siguientes opciones.

Tipo de almuerzo: {{ $data['type_name'] }}  
@if ($data['starter_name'])
Entrada: {{ $data['starter_name'] }}  
@endif
Plato principal: {{ $data['main_name'] }}


Gracias,<br>
{{ config('app.name') }}
@endcomponent
