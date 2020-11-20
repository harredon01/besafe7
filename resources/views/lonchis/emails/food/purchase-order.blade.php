@component('mail::message')

# Detalles de la orden de compra

@foreach ($data['totals']['totals'] as $line)
{{ $line }}  
@endforeach  

@foreach ($data['totals']['keywords'] as $line)
{{ $line }}  
@endforeach 

@foreach ($data['totals']['dish'] as $line)
{{ $line }}  
@endforeach 

Gracias,<br>
{{ config('app.name') }}
@endcomponent
