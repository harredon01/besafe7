@component('mail::message')

# Hola {{$user->firstName}}!

Se te ha enviado un codigo para verificar tu identidad. Porfavor ingresalo en nuestra app para completar la verificación. Si no fuiste tu simplemente ignora este mensaje. 

{{$token}}

Gracias,<br>
{{ config('app.name') }}
@endcomponent