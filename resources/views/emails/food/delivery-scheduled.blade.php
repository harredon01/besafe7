@component('mail::message')

# Almuerzo programado
Tu almuerzo quedo programado para entrega el {{ $data['date'] }}   con las siguientes opciones.

Tipo de almuerzo: {{ $data['type_name'] }}  
@if ($data['starter_name'])
Entrada: {{ $data['starter_name'] }}  
@endif
Plato principal: {{ $data['main_name'] }}

Direccion: {{ $address->address }}

El cambio de direccion de entrega despues de las 7am del dia de la entrega tendra un costo adicional de $6,000 pesos. 

Gracias,<br>
{{ config('app.name') }}
@endcomponent
