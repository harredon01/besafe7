@component('mail::message')

{{$bodyMail}} 

Gracias,<br>
{{ config('app.name') }}
@endcomponent
