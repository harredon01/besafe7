@component('mail::message')

{{$subject}} 

{{$bodyMail}} 

Gracias,<br>
{{ config('app.name') }}
@endcomponent
